<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_scheduling_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attendance_score')->default(50);
            $table->unsignedTinyInteger('popularity_score')->default(50);
            $table->integer('priority_points')->default(0);
            $table->date('newcomer_priority_until')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'priority_points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_scheduling_profiles');
    }
};
