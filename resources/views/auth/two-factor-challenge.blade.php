<x-guest-layout>
    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-6 text-left">
        @csrf

        <div class="space-y-3">
            <p class="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black text-teal-700 ring-1 ring-teal-200">2段階認証</p>
            <h2 class="text-3xl font-black leading-tight text-slate-950 sm:text-4xl">認証コード</h2>
            <p class="text-sm leading-6 text-slate-600">認証アプリに表示された6桁のコードを入力してください。</p>
        </div>

        <div class="space-y-2">
            <x-input-label for="code" :value="__('認証コード')" />
            <x-text-input
                id="code"
                class="block w-full text-center text-lg tracking-[0.25em]"
                type="text"
                inputmode="numeric"
                name="code"
                autofocus
                autocomplete="one-time-code"
                placeholder="000000"
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <details class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <summary class="cursor-pointer text-sm font-black text-slate-700">リカバリーコードを使う</summary>
            <div class="mt-4 space-y-2">
                <x-input-label for="recovery_code" :value="__('リカバリーコード')" />
                <x-text-input
                    id="recovery_code"
                    class="block w-full"
                    type="text"
                    name="recovery_code"
                    autocomplete="one-time-code"
                    placeholder="xxxx-xxxxxx"
                />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>
        </details>

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-gradient-primary w-full sm:w-auto">
                {{ __('認証する') }}
            </button>
        </div>
    </form>
</x-guest-layout>
