<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorResetMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

class TwoFactorResetEmailController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login.id');

        if (! $userId || ! $user = User::query()->find($userId)) {
            return redirect()->route('login');
        }

        $throttleKey = "two-factor-reset-email:{$user->id}:{$request->ip()}";
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors([
                'recovery_code' => '送信回数が多すぎます。しばらく待ってから再度お試しください。',
            ]);
        }

        RateLimiter::hit($throttleKey, 600);

        if (! $user->email) {
            return back()->withErrors([
                'recovery_code' => '再設定リンクを送信できるメールアドレスがありません。',
            ]);
        }

        $resetUrl = URL::temporarySignedRoute(
            'two-factor.reset',
            now()->addMinutes(30),
            [
                'user' => $user->id,
                'email' => sha1($user->email),
            ],
            false,
        );

        Mail::to($user->email)->send(new TwoFactorResetMail(url($resetUrl)));

        return back()->with('status', '登録メールアドレス宛に2段階認証の再設定リンクを送信しました。');
    }

    public function reset(Request $request, User $user): RedirectResponse
    {
        abort_unless(hash_equals(sha1((string) $user->email), (string) $request->query('email')), 403);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->forget('login.id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('two-factor.settings')
            ->with('status', '2段階認証を解除しました。新しい認証アプリを設定してください。');
    }
}
