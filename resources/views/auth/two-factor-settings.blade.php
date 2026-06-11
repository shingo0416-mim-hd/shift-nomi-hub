@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $isEnabled = $user->hasEnabledTwoFactorAuthentication();
    $hasPendingSecret = ! is_null($user->two_factor_secret) && ! $isEnabled;
    $recoveryCodes = $user->two_factor_recovery_codes ? $user->recoveryCodes() : [];
    $statusMessages = [
        \Laravel\Fortify\Fortify::TWO_FACTOR_AUTHENTICATION_ENABLED => 'QRコードを読み取り、認証コードを入力して有効化を完了してください。',
        \Laravel\Fortify\Fortify::TWO_FACTOR_AUTHENTICATION_CONFIRMED => '2段階認証を有効化しました。',
        \Laravel\Fortify\Fortify::TWO_FACTOR_AUTHENTICATION_DISABLED => '2段階認証を無効化しました。',
        \Laravel\Fortify\Fortify::RECOVERY_CODES_GENERATED => 'リカバリーコードを再発行しました。',
    ];
@endphp

<x-guest-layout>
    <div class="space-y-6 text-left">
        <div class="space-y-3">
            <p class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-800 ring-1 ring-amber-200">管理者必須</p>
            <h2 class="text-3xl font-black leading-tight text-slate-950 sm:text-4xl">2段階認証設定</h2>
            <p class="text-sm leading-6 text-slate-600">認証アプリを設定すると、次回以降の管理ログインで確認コードが必要になります。</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                {{ $statusMessages[session('status')] ?? session('status') }}
            </div>
        @endif

        @if (! $user->two_factor_secret)
            <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <div>
                    <h3 class="text-base font-black text-slate-950">認証アプリを設定する</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Google Authenticator、Microsoft Authenticator、1Password などのTOTP対応アプリを利用できます。</p>
                </div>

                <button type="submit" class="btn-gradient-primary">
                    {{ __('設定を開始する') }}
                </button>
            </form>
        @endif

        @if ($hasPendingSecret)
            <div class="space-y-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div>
                    <h3 class="text-base font-black text-slate-950">QRコードを読み取る</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">認証アプリでQRコードを読み取り、表示された6桁のコードを入力してください。</p>
                </div>

                <div class="mx-auto grid size-56 place-items-center rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    {!! $user->twoFactorQrCodeSvg() !!}
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-3">
                    @csrf
                    <div class="space-y-2">
                        <x-input-label for="code" :value="__('認証コード')" />
                        <x-text-input
                            id="code"
                            class="block w-full text-center text-lg tracking-[0.25em]"
                            type="text"
                            name="code"
                            inputmode="numeric"
                            required
                            autocomplete="one-time-code"
                            placeholder="000000"
                        />
                        <x-input-error :messages="$errors->getBag('confirmTwoFactorAuthentication')->get('code')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn-gradient-primary">
                        {{ __('有効化する') }}
                    </button>
                </form>
            </div>
        @endif

        @if ($isEnabled)
            <div class="space-y-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <div>
                    <h3 class="text-base font-black text-emerald-900">2段階認証は有効です</h3>
                    <p class="mt-1 text-sm leading-6 text-emerald-800">次回ログイン時から、パスワード確認後に認証コードの入力画面が表示されます。</p>
                </div>

                @if ($recoveryCodes)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <h4 class="text-sm font-black text-slate-950">リカバリーコード</h4>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($recoveryCodes as $code)
                                <code class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:border-teal-500 hover:text-teal-800">
                            {{ __('コード再発行') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.disable') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-black text-red-700 transition hover:bg-red-100">
                            {{ __('無効化する') }}
                        </button>
                    </form>

                    <a href="{{ route('dashboard') }}" class="rounded-xl border border-teal-700 bg-teal-700 px-4 py-2 text-sm font-black text-white transition hover:bg-teal-800">
                        {{ __('ダッシュボードへ') }}
                    </a>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-sm font-bold text-slate-500 transition hover:text-teal-700">
                {{ __('ログアウト') }}
            </button>
        </form>
    </div>
</x-guest-layout>
