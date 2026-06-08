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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name')->nullable()->comment('ユーザー名');
            $table->string('last_name')->nullable()->comment('姓');
            $table->string('first_name')->nullable()->comment('名');
            $table->string('icon_url')->nullable()->comment('アイコンURL');
            $table->string('email')->nullable()->unique()->comment('メールアドレス');
            $table->timestamp('email_verified_at')->nullable()->comment('メールアドレス確認日時');
            $table->string('password')->nullable()->comment('パスワード');
            $table->text('two_factor_secret')->nullable()->comment('二要素認証シークレット');
            $table->text('two_factor_recovery_codes')->nullable()->comment('二要素認証リカバリーコード');
            $table->timestamp('two_factor_confirmed_at')->nullable()->comment('二要素認証確認日時');
            $table->rememberToken()->comment('リメンバートークン');
            $table->string('phone')->nullable()->comment('電話番号');
            $table->string('company')->nullable()->comment('会社名');
            $table->string('employees')->nullable()->comment('社員数');
            $table->string('company_type')->nullable()->comment('会社の種別');
            $table->tinyInteger('role')->default(0)->comment('権限レベル: 0=一般ユーザー, 1=管理者, 2=スーパー管理者');
            $table->json('ips')->nullable()->comment('許可IPアドレスリスト');
            $table->timestamp('login_at')->nullable()->comment('ログイン日時');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('作成者');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('最終更新者');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->comment('削除者');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
