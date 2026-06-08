<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-6 text-left">
        @csrf

        <div class="space-y-2 text-center">
            <h2 class="text-3xl font-bold text-blue-900">新規登録</h2>
            <p class="text-sm text-slate-500">店舗シフト管理を開始する管理者アカウントを作成します。</p>
        </div>

        <div class="space-y-2">
            <x-input-label for="tenant_name" :value="__('会社名・組織名')" />
            <x-text-input id="tenant_name" class="block w-full" type="text" name="tenant_name" :value="old('tenant_name')" required autofocus autocomplete="organization" placeholder="例: 株式会社シフトハブ" />
            <x-input-error :messages="$errors->get('tenant_name')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="store_name" :value="__('店舗名')" />
            <x-text-input id="store_name" class="block w-full" type="text" name="store_name" :value="old('store_name')" autocomplete="off" placeholder="例: 渋谷店" />
            <x-input-error :messages="$errors->get('store_name')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="name" :value="__('管理者名')" />
            <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autocomplete="name" placeholder="山田 太郎" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="admin@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('パスワード')" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" placeholder="8文字以上" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password_confirmation" :value="__('パスワード確認')" />
            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="もう一度入力" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            <a class="text-sm font-medium text-blue-600 transition-colors hover:text-blue-500" href="{{ route('login') }}">
                {{ __('ログインはこちら') }}
            </a>

            <button type="submit" class="btn-gradient-primary">
                {{ __('登録する') }}
            </button>
        </div>
    </form>
</x-guest-layout>
