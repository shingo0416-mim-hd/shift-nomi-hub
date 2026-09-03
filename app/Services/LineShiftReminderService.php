<?php

namespace App\Services;

use App\Models\Member;
use App\Models\ShiftSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineShiftReminderService
{
    public function sendDueReminders(): int
    {
        $sentCount = 0;
        $schedules = ShiftSchedule::query()
            ->where('status', 'draft')
            ->where('auto_schedule_enabled', true)
            ->whereNull('auto_scheduled_at')
            ->where('submission_deadline_at', '>', now())
            ->where('submission_deadline_at', '<=', now()->addDays(3))
            ->with(['tenant.lineOfficialAccount', 'days'])
            ->get();

        foreach ($schedules as $schedule) {
            $sentCount += $this->sendForSchedule($schedule);
        }

        return $sentCount;
    }

    private function sendForSchedule(ShiftSchedule $schedule): int
    {
        $account = $schedule->tenant?->lineOfficialAccount;
        if (! $account?->is_active || ! $account->channel_access_token) {
            Log::warning('シフト提出リマインドを送信できません。LINE公式アカウントが未設定です。', ['shift_schedule_id' => $schedule->id]);

            return 0;
        }

        $expectedDays = $schedule->days->pluck('scheduled_on')->map->toDateString()->unique();
        if ($expectedDays->isEmpty()) {
            return 0;
        }

        $storeIds = $schedule->days->where('is_day_off', false)->pluck('store_id')->push($schedule->store_id)->unique();
        $members = Member::query()
            ->where('tenant_id', $schedule->tenant_id)
            ->where('status', 'active')
            ->where('is_shift_submitter', true)
            ->where('is_remind_disabled', false)
            ->whereIn('store_id', $storeIds)
            ->whereNotNull('line_id')
            ->withCount(['availabilityRequests as submitted_days_count' => fn ($query) => $query
                ->whereBetween('work_date', [$schedule->starts_on, $schedule->ends_on])])
            ->get()
            ->filter(fn (Member $member) => $member->submitted_days_count < $expectedDays->count());

        $remainingSeconds = max(0, now()->diffInSeconds($schedule->submission_deadline_at, false));
        $remainingDays = max(1, (int) ceil($remainingSeconds / 86400));
        $notificationType = $remainingSeconds <= 86400 ? 'submission_reminder_1d' : 'submission_reminder_3d';
        $sentCount = 0;

        foreach ($members as $member) {
            if ($this->wasSent($schedule, $member, $notificationType)) {
                continue;
            }

            $missingDays = max(0, $expectedDays->count() - $member->submitted_days_count);
            $message = "{$member->displayName()}さん\nシフト希望の提出期限まであと{$remainingDays}日です。\n期限: {$schedule->submission_deadline_at->format('n/j H:i')}\n未入力: {$missingDays}日分\n\nLINEミニアプリから希望シフトを提出してください。";
            $response = Http::withToken($account->channel_access_token)
                ->post(rtrim(config('services.messaging-api.base_url'), '/').'/v2/bot/message/push', [
                    'to' => $member->line_id,
                    'messages' => [['type' => 'text', 'text' => $message]],
                ]);

            $this->recordDelivery($schedule, $member, $notificationType, $response->successful(), $response->status());
            if ($response->successful()) {
                $sentCount++;
            } else {
                Log::error('LINEシフト提出リマインドの送信に失敗しました。', [
                    'shift_schedule_id' => $schedule->id,
                    'member_id' => $member->id,
                    'status' => $response->status(),
                ]);
            }
        }

        return $sentCount;
    }

    private function wasSent(ShiftSchedule $schedule, Member $member, string $notificationType): bool
    {
        return DB::table('shift_notification_deliveries')
            ->where('shift_schedule_id', $schedule->id)
            ->where('member_id', $member->id)
            ->where('notification_type', $notificationType)
            ->where('status', 'sent')
            ->exists();
    }

    private function recordDelivery(ShiftSchedule $schedule, Member $member, string $notificationType, bool $successful, int $status): void
    {
        DB::table('shift_notification_deliveries')->updateOrInsert(
            [
                'shift_schedule_id' => $schedule->id,
                'member_id' => $member->id,
                'notification_type' => $notificationType,
            ],
            [
                'tenant_id' => $schedule->tenant_id,
                'status' => $successful ? 'sent' : 'failed',
                'sent_at' => $successful ? now() : null,
                'last_error' => $successful ? null : "HTTP {$status}",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
