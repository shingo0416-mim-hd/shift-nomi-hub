<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6 text-left">
        @csrf

        <div class="space-y-2 text-center">
            <h2 class="text-3xl font-black text-slate-950">パスワード再設定</h2>
            <p class="text-sm text-slate-500">登録メールアドレスに再設定リンクを送信します。</p>
        </div>

        <div class="space-y-2">
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="admin@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-sm font-bold text-teal-700 transition-colors hover:text-teal-900" href="{{ route('login') }}">
                {{ __('ログインに戻る') }}
            </a>

            <button type="submit" class="btn-gradient-primary w-full sm:w-auto">
                {{ __('送信する') }}
            </button>
        </div>
    </form>
</x-guest-layout>
