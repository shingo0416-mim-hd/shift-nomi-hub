<x-guest-layout>
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6 text-left">
        @csrf

        <div class="space-y-2 text-center">
            <h2 class="text-3xl font-bold text-blue-900">パスワード確認</h2>
            <p class="text-sm text-slate-500">操作を続けるため、現在のパスワードを入力してください。</p>
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('パスワード')" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-gradient-primary">
                {{ __('確認する') }}
            </button>
        </div>
    </form>
</x-guest-layout>
