<x-guest-layout>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-6 text-left">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-2 text-center">
            <h2 class="text-3xl font-bold text-blue-900">新しいパスワード</h2>
            <p class="text-sm text-slate-500">ログインに使用する新しいパスワードを設定します。</p>
        </div>

        <div class="space-y-2">
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('パスワード')" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password_confirmation" :value="__('パスワード確認')" />
            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-gradient-primary">
                {{ __('更新する') }}
            </button>
        </div>
    </form>
</x-guest-layout>
