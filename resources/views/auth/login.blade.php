<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6 text-left">
        @csrf

        <div class="text-center space-y-2">
            <h2 class="text-3xl font-bold text-blue-900">ログイン</h2>
            <p class="text-sm text-slate-500">アカウント情報を入力し、シフト管理をはじめましょう。</p>
        </div>

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input
                id="email"
                class="block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="メールアドレスを入力"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <x-input-label for="password" :value="__('パスワード')" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="パスワードを入力"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 bg-white text-blue-600 shadow-sm focus:ring-blue-400 focus:ring-offset-1 focus:ring-offset-white accent-blue-500 transition" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('ログイン状態を保持する') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-2">
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 focus:ring-offset-white rounded-md px-1 py-0.5" href="{{ route('password.request') }}">
                    {{ __('パスワードをお忘れですか？') }}
                </a>
            @endif

            <div class="relative inline-flex items-center gap-3">
                <button type="submit" class="btn-gradient-primary" data-loading-text="{{ __('ログイン中...') }}" data-show-success="false">
                    <svg class="hidden h-4 w-4 animate-spin" aria-hidden="true" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="button-label">{{ __('ログイン') }}</span>
                </button>
            </div>
        </div>
    </form>
</x-guest-layout>
