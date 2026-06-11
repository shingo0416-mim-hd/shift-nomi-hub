<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-7 text-left">
        @csrf

        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <p class="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black text-teal-700 ring-1 ring-teal-200">2段階認証対応</p>
                <p class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800 ring-1 ring-amber-200">管理者専用</p>
            </div>
            <div class="space-y-2">
                <h2 class="text-3xl font-black leading-tight text-slate-950 sm:text-4xl">管理ログイン</h2>
                <p class="max-w-md text-sm leading-6 text-slate-600">メールアドレスとパスワードを確認後、認証アプリの6桁コードを入力します。</p>
            </div>
        </div>

        <div class="space-y-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
            <!-- Email Address -->
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <x-input-label for="email" :value="__('メールアドレス')" />
                    <span class="text-xs font-semibold text-slate-400">Admin ID</span>
                </div>
                <x-text-input
                    id="email"
                    class="block w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="admin@example.com"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <x-input-label for="password" :value="__('パスワード')" />

                    @if (Route::has('password.request'))
                        <a class="text-xs font-bold text-teal-700 transition-colors hover:text-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-100" href="{{ route('password.request') }}">
                            {{ __('お忘れですか？') }}
                        </a>
                    @endif
                </div>

                <x-text-input
                    id="password"
                    class="block w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="パスワードを入力"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
        </div>

        <!-- Remember Me -->
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <label for="remember_me" class="flex items-start gap-3">
                <input id="remember_me" type="checkbox" class="mt-1 rounded border-slate-300 text-teal-700 accent-teal-700 transition focus:ring-4 focus:ring-teal-100" name="remember">
                <span>
                    <span class="block text-sm font-bold text-slate-800">{{ __('ログイン状態を保持する') }}</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">共有端末ではチェックを外してください。</span>
                </span>
            </label>
        </div>

        <div class="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-h-5">
                @if (Route::has('register'))
                    <a class="text-sm font-bold text-teal-700 transition-colors hover:text-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-100" href="{{ route('register') }}">
                        {{ __('管理者アカウントを作成') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-gradient-primary w-full sm:w-auto" data-loading-text="{{ __('ログイン中...') }}" data-show-success="false">
                <svg class="hidden h-4 w-4 animate-spin" aria-hidden="true" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="button-label">{{ __('ログイン') }}</span>
            </button>
        </div>
    </form>
</x-guest-layout>
