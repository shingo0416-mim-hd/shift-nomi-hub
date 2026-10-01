<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WorkforceMessaging
{
    public function create(int $tenantId, string $title, string $body, ?int $storeId = null, ?int $senderUser = null, ?int $senderMember = null): int
    {
        return DB::transaction(function () use ($tenantId, $title, $body, $storeId, $senderUser, $senderMember) {
            $id = DB::table('workforce_messages')->insertGetId(['tenant_id' => $tenantId, 'store_id' => $storeId, 'title' => $title, 'body' => $body, 'sender_user_id' => $senderUser, 'sender_member_id' => $senderMember, 'created_at' => now(), 'updated_at' => now()]);
            if (! $senderMember) {
                Member::where('tenant_id', $tenantId)->where('status', 'active')->when($storeId, fn ($q) => $q->where('store_id', $storeId))->orderBy('id')->chunkById(200, function ($members) use ($id) {
                    DB::table('workforce_message_recipients')->insert($members->map(fn ($m) => ['workforce_message_id' => $id, 'member_id' => $m->id, 'delivery_status' => 'pending'])->all());
                });
            }

            return $id;
        });
    }

    public function deliver(): int
    {
        $count = 0;
        $eligible = fn () => DB::table('workforce_message_recipients')
            ->whereIn('delivery_status', ['pending', 'failed', 'unavailable', 'sending'])
            ->where('attempts', '<', 6)
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()));
        foreach ($eligible()->orderBy('id')->limit(100)->pluck('id') as $id) {
            $recipient = DB::transaction(function () use ($eligible, $id) {
                $row = $eligible()->where('id', $id)->lockForUpdate()->first();
                if (! $row) {
                    return null;
                }
                if ($row->first_attempt_at && CarbonImmutable::parse($row->first_attempt_at)->lte(now()->subHours(23))) {
                    DB::table('workforce_message_recipients')->where('id', $id)->update(['delivery_status' => 'expired']);

                    return null;
                }
                DB::table('workforce_message_recipients')->where('id', $id)->update(['delivery_status' => 'sending', 'next_attempt_at' => now()->addMinutes(5)]);

                return $row;
            });
            if (! $recipient) {
                continue;
            }
            $message = DB::table('workforce_messages')->find($recipient->workforce_message_id);
            $member = Member::find($recipient->member_id);
            $account = Tenant::find($message->tenant_id)?->lineOfficialAccount;
            if (! $member || $member->status !== 'active' || ! $member->line_id || ! $account?->is_active || ! $account->channel_access_token) {
                DB::table('workforce_message_recipients')->where('id', $id)->update(['delivery_status' => 'unavailable', 'next_attempt_at' => now()->addMinutes(15)]);

                continue;
            }
            $key = $recipient->retry_key ?: (string) Str::uuid();
            $destination = $recipient->line_destination ?: $member->line_id;
            DB::table('workforce_message_recipients')->where('id', $id)->update(['retry_key' => $key, 'line_destination' => $destination, 'first_attempt_at' => $recipient->first_attempt_at ?: now(), 'attempts' => $recipient->attempts + 1]);
            try {
                $response = Http::timeout(15)->withToken($account->channel_access_token)->withHeaders(['X-Line-Retry-Key' => $key])->post(rtrim(config('services.messaging-api.base_url'), '/') . '/v2/bot/message/push', ['to' => $destination, 'messages' => [['type' => 'text', 'text' => $message->title . "\n\n" . $message->body]]]);
                $ok = $response->successful() || ($response->status() === 409 && $response->header('x-line-accepted-request-id'));
                $status = $ok ? 'sent' : ($response->serverError() ? 'failed' : 'rejected');
            } catch (\Throwable) {
                $ok = false;
                $status = 'failed';
            }
            DB::table('workforce_message_recipients')->where('id', $id)->update(['delivery_status' => $status, 'notified_at' => $ok ? now() : null, 'next_attempt_at' => $ok ? null : now()->addMinutes(2 ** ($recipient->attempts + 1))]);
            if ($ok) {
                $count++;
            }
        }

        return $count;
    }
}
