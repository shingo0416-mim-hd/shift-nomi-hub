<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('shift_schedule_days')) {
            return;
        }

        Schema::table('shift_schedule_days', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_schedule_days', 'is_day_off')) {
                $table->boolean('is_day_off')->default(false)->after('scheduled_on');
            }

            if (! Schema::hasColumn('shift_schedule_days', 'starts_at')) {
                $table->time('starts_at')->nullable()->after('is_day_off');
            }

            if (! Schema::hasColumn('shift_schedule_days', 'ends_at')) {
                $table->time('ends_at')->nullable()->after('starts_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('shift_schedule_days')) {
            return;
        }

        Schema::table('shift_schedule_days', function (Blueprint $table) {
            foreach (['ends_at', 'starts_at', 'is_day_off'] as $column) {
                if (Schema::hasColumn('shift_schedule_days', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
