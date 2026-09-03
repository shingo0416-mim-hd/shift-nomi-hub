<?php

namespace App\Console\Commands;

use App\Services\LineShiftReminderService;
use Illuminate\Console\Command;

class SendShiftSubmissionReminders extends Command
{
    protected $signature = 'shifts:send-reminders';

    protected $description = '未提出メンバーへシフト提出期限のLINEリマインドを送信する';

    public function handle(LineShiftReminderService $reminders): int
    {
        $count = $reminders->sendDueReminders();
        $this->info("{$count}件のリマインドを送信しました。");

        return self::SUCCESS;
    }
}
