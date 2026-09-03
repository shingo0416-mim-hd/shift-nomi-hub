<?php

namespace App\Console\Commands;

use App\Services\ShiftAutoScheduler;
use Illuminate\Console\Command;

class AutoFinalizeShiftSchedules extends Command
{
    protected $signature = 'shifts:auto-finalize';

    protected $description = '提出期限を過ぎたシフトを自動編成してLINE通知する';

    public function handle(ShiftAutoScheduler $scheduler): int
    {
        $count = $scheduler->processDueSchedules();
        $this->info("{$count}件のシフトを自動編成しました。");

        return self::SUCCESS;
    }
}
