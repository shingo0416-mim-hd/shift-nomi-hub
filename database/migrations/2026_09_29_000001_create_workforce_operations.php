<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('settings');
            $table->timestamps();
        });
        Schema::create('member_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('skill', 100);
            $table->unsignedTinyInteger('level');
            $table->date('expires_on')->nullable();
            $table->unique(['member_id', 'skill']);
            $table->timestamps();
        });
        Schema::create('work_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_slot_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('skill', 100)->nullable();
            $table->unsignedTinyInteger('minimum_level')->default(1);
            $table->unsignedInteger('headcount')->default(1);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();
        });
        Schema::create('work_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->unique(['work_task_id', 'member_id']);
            $table->timestamps();
        });
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dateTime('break_starts_at')->nullable();
            $table->dateTime('break_ends_at')->nullable();
        });
        Schema::create('shift_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_slot_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->text('message');
            $table->boolean('allow_help')->default(false);
            $table->timestamps();
        });
        Schema::create('vacancy_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->unique(['shift_vacancy_id', 'member_id']);
            $table->timestamps();
        });
        Schema::create('workforce_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sender_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->text('body');
            $table->timestamps();
        });
        Schema::create('workforce_message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workforce_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->string('delivery_status')->default('pending');
            $table->uuid('retry_key')->nullable();
            $table->string('line_destination')->nullable();
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->unique(['workforce_message_id', 'member_id'], 'workforce_message_member_unique');
        });
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('external_id', 100);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('break_minutes')->default(0);
            $table->unique(['tenant_id', 'external_id']);
            $table->timestamps();
        });
        Schema::create('pos_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->dateTime('interval_start');
            $table->unsignedInteger('customers');
            $table->decimal('sales', 14, 2);
            $table->unique(['store_id', 'interval_start']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['pos_records', 'attendance_records', 'workforce_message_recipients', 'workforce_messages', 'vacancy_applications', 'shift_vacancies', 'work_task_assignments', 'work_tasks', 'member_skills', 'workforce_policies'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropColumn(['break_starts_at', 'break_ends_at']);
        });
    }
};
