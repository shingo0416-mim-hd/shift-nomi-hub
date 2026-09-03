<?php

namespace App\Services;

use App\Models\Member;
use App\Models\ShiftSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineShiftNotificationService
{
    public function send(ShiftSchedule $schedule): bool
    {
        $account = $schedule->tenant?->lineOfficialAccount;
        if (! $account?->is_active || ! $account->channel_access_token) {
            Log::warning('シフト通知を送信できません。LINE公式アカウントが未設定です。', ['shift_schedule_id' => $schedule->id]);

            return false;
        }

        $slotsByMember = [];
        foreach ($schedule->shiftSlots as $slot) {
            foreach ($slot->assignments as $assignment) {
                if (! $assignment->member?->line_id || $assignment->status === 'cancelled') {
                    continue;
                }
                $slotsByMember[$assignment->member_id]['member'] = $assignment->member;
                $slotsByMember[$assignment->member_id]['slots'][] = $slot;
            }
        }

        $allSent = true;
        foreach ($slotsByMember as $entry) {
            /** @var Member $member */
            $member = $entry['member'];
            $alreadySent = DB::table('shift_notification_deliveries')
                ->where('shift_schedule_id', $schedule->id)
                ->where('member_id', $member->id)
                ->where('status', 'sent')
                ->exists();
            if ($alreadySent) {
                continue;
            }
            $lines = collect($entry['slots'])->sortBy('starts_at')->map(fn ($slot) => sprintf(
                '%s %s-%s%s',
                $slot->starts_at->format('n/j'),
                $slot->starts_at->format('H:i'),
                $slot->ends_at->format('H:i'),
                $slot->notes ? " {$slot->notes}" : '',
            ));
            $message = "{$member->displayName()}さん\nシフトが確定しました。\n\n".$lines->implode("\n");

            $response = Http::withToken($account->channel_access_token)
                ->post(rtrim(config('services.messaging-api.base_url'), '/').'/v2/bot/message/push', [
                    'to' => $member->line_id,
                    'messages' => [['type' => 'text', 'text' => $message]],
                ]);

            if ($response->failed()) {
                $allSent = false;
                DB::table('shift_notification_deliveries')->updateOrInsert(
                    ['shift_schedule_id' => $schedule->id, 'member_id' => $member->id],
                    [
                        'tenant_id' => $schedule->tenant_id,
                        'status' => 'failed',
                        'sent_at' => null,
                        'last_error' => "HTTP {$response->status()}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
                Log::error('LINEシフト通知の送信に失敗しました。', [
                    'shift_schedule_id' => $schedule->id,
                    'member_id' => $member->id,
                    'status' => $response->status(),
                ]);
            } else {
                DB::table('shift_notification_deliveries')->updateOrInsert(
                    ['shift_schedule_id' => $schedule->id, 'member_id' => $member->id],
                    [
                        'tenant_id' => $schedule->tenant_id,
                        'status' => 'sent',
                        'sent_at' => now(),
                        'last_error' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }

        if ($allSent) {
            $schedule->update(['notification_sent_at' => now()]);
        }

        return $allSent;
    }
}
