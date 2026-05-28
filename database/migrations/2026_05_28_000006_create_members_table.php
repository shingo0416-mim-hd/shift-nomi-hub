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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete()->comment('店舗ID');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable()->comment('氏名');
            $table->string('last_name')->nullable()->comment('姓');
            $table->string('first_name')->nullable()->comment('名');
            $table->string('name_kana')->nullable()->comment('氏名かな');
            $table->string('last_name_kana')->nullable()->comment('姓かな');
            $table->string('first_name_kana')->nullable()->comment('名かな');
            $table->string('line_id')->nullable()->comment('LINE ID');
            $table->string('line_name')->nullable()->comment('LINE名');
            $table->string('icon_url')->nullable()->comment('アイコンURL');
            $table->string('cline_id')->nullable()->comment('CLINE登録者No');
            $table->string('cline_url')->nullable()->comment('CLINE URL');
            $table->string('company')->nullable()->comment('会社名');
            $table->string('gender')->nullable()->comment('性別');
            $table->string('phone')->nullable()->comment('電話番号');
            $table->string('email')->nullable()->comment('メールアドレス');
            $table->string('password')->nullable()->comment('パスワード');
            $table->string('birth_date')->nullable()->comment('生年月日');
            $table->string('birth_year')->nullable()->comment('生年');
            $table->string('birth_month')->nullable()->comment('生月');
            $table->string('birth_day')->nullable()->comment('生日');
            $table->string('postal_code')->nullable()->comment('郵便番号');
            $table->string('country')->nullable()->comment('国');
            $table->text('address')->nullable()->comment('住所');
            $table->string('prefecture')->nullable()->comment('都道府県');
            $table->string('city')->nullable()->comment('市区町村');
            $table->string('street_address')->nullable()->comment('番地');
            $table->string('status', 50)->nullable()->comment('ステータス');
            $table->text('comment')->nullable()->comment('ステータスコメント');
            $table->text('remarks')->nullable()->comment('備考');
            $table->boolean('is_shift_submitter')->default(true)->comment('シフト提出対象者かどうか');
            $table->boolean('is_linked')->default(false)->comment('LINE連携済みかどうか');
            $table->boolean('is_remind_disabled')->default(false)->comment('リマインド停止中かどうか');
            $table->json('profiles')->nullable()->comment('プロフィール情報');
            $table->json('tags')->nullable()->comment('タグ');
            $table->ipAddress('ip_address')->nullable()->comment('IPアドレス');
            $table->timestamp('login_at')->nullable()->comment('ログイン日時');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('作成者');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('最終更新者');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->comment('削除者');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'line_id']);
            $table->index(['tenant_id', 'store_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'is_shift_submitter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
