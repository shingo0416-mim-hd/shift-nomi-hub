<?php

namespace Tests\Feature;

use App\Mail\TwoFactorResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

class TwoFactorResetEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_can_be_sent_to_challenged_users_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt('secret'),
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['old-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge/reset/email')
            ->assertRedirect()
            ->assertSessionHas('status', '登録メールアドレス宛に2段階認証の再設定リンクを送信しました。');

        Mail::assertSent(TwoFactorResetMail::class, function (TwoFactorResetMail $mail): bool {
            return $mail->hasTo('admin@example.com')
                && str_contains($mail->resetUrl, '/two-factor-reset/');
        });
    }

    public function test_reset_mail_does_not_escape_signed_url_query_separator(): void
    {
        $mail = new TwoFactorResetMail('https://example.test/two-factor-reset/1?email=abc&expires=123&signature=xyz');

        $rendered = $mail->render();

        $this->assertStringContainsString('email=abc&expires=123&signature=xyz', $rendered);
        $this->assertStringNotContainsString('&amp;', $rendered);
    }

    public function test_reset_email_redirects_to_login_without_challenged_user(): void
    {
        Mail::fake();

        $this->post('/two-factor-challenge/reset/email')
            ->assertRedirect('/login');

        Mail::assertNothingSent();
    }

    public function test_signed_reset_link_clears_two_factor_and_redirects_to_settings(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt('secret'),
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['old-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $url = URL::temporarySignedRoute('two-factor.reset', now()->addMinutes(30), [
            'user' => $user->id,
            'email' => sha1($user->email),
        ], false);

        $this->get($url)
            ->assertRedirect(route('two-factor.settings', absolute: false))
            ->assertSessionHas('status', '2段階認証を解除しました。新しい認証アプリを設定してください。');

        $user->refresh();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertAuthenticatedAs($user);
    }
}
