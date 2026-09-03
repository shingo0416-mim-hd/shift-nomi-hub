<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_notification_deliveries', function (Blueprint $table): void {
            $table->dropUnique(['shift_schedule_id', 'member_id']);
            $table->string('notification_type')->default('shift_confirmed')->after('member_id');
            $table->unique(['shift_schedule_id', 'member_id', 'notification_type'], 'shift_notification_unique');
        });
    }

    public function down(): void
    {
        Schema::table('shift_notification_deliveries', function (Blueprint $table): void {
            $table->dropUnique('shift_notification_unique');
            $table->dropColumn('notification_type');
            $table->unique(['shift_schedule_id', 'member_id']);
        });
    }
};
