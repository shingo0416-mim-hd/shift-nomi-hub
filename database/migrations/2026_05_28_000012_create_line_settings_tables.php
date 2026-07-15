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
        Schema::create('line_login_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('channel_id')->nullable()->comment('LINEログインチャネルID');
            $table->text('channel_secret')->nullable()->comment('LINEログインチャネルシークレット');
            $table->boolean('is_active')->default(true)->comment('有効フラグ');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);

            $table->unique('tenant_id');
        });

        Schema::create('line_liff_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('liff_id')->nullable()->comment('LIFF ID');
            $table->string('description')->nullable()->comment('説明');
            $table->boolean('is_active')->default(true)->comment('有効フラグ');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);

            $table->unique('tenant_id');
        });

        Schema::create('line_official_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('channel_id')->nullable()->comment('公式LINEチャネルID');
            $table->text('channel_access_token')->nullable()->comment('公式LINEチャネルアクセストークン');
            $table->text('channel_secret')->nullable()->comment('公式LINEチャネルシークレット');
            $table->string('webhook_url')->nullable()->comment('Webhook URL');
            $table->string('line_at_id')->nullable()->comment('LINE公式アカウントID');
            $table->string('line_timeline_url')->nullable()->comment('LINEタイムラインURL');
            $table->boolean('is_active')->default(true)->comment('有効フラグ');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);

            $table->unique('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_official_accounts');
        Schema::dropIfExists('line_liff_settings');
        Schema::dropIfExists('line_login_settings');
    }
};
