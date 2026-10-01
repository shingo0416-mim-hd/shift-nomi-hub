<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('shifts:auto-finalize')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('shifts:send-reminders')
    ->hourly()
    ->withoutOverlapping();

Artisan::command('workforce:deliver-messages', function () {
    $this->info(app(\App\Services\WorkforceMessaging::class)->deliver().'件配信しました。');
})->purpose('登録済みのお知らせ・欠員募集をLINEに配信');

Schedule::command('workforce:deliver-messages')->everyMinute()->withoutOverlapping();
