<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_schedules', function (Blueprint $table): void {
            $table->dateTime('submission_deadline_at')->nullable()->after('ends_on');
            $table->boolean('auto_schedule_enabled')->default(true)->after('submission_deadline_at');
            $table->dateTime('auto_scheduled_at')->nullable()->after('auto_schedule_enabled');
            $table->dateTime('notification_sent_at')->nullable()->after('auto_scheduled_at');
        });

        Schema::table('shift_schedule_days', function (Blueprint $table): void {
            $table->unsignedSmallInteger('required_headcount')->default(1)->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('shift_schedule_days', function (Blueprint $table): void {
            $table->dropColumn('required_headcount');
        });

        Schema::table('shift_schedules', function (Blueprint $table): void {
            $table->dropColumn([
                'submission_deadline_at',
                'auto_schedule_enabled',
                'auto_scheduled_at',
                'notification_sent_at',
            ]);
        });
    }
};
