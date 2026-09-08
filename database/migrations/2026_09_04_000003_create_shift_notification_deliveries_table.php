<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type')->default('shift_confirmed');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['shift_schedule_id', 'member_id', 'notification_type'], 'shift_notification_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_notification_deliveries');
    }
};
