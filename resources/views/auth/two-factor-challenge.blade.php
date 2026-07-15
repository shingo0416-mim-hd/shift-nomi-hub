<x-guest-layout>
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">
            {{ session('status') }}
        </div>
    @endif

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
                <p class="text-sm leading-6 text-slate-600">2段階認証設定時に発行されたリカバリーコードのうち、未使用のコードを1つだけ入力してください。</p>
                <x-input-label for="recovery_code" :value="__('リカバリーコード')" />
                <x-text-input
                    id="recovery_code"
                    class="block w-full"
                    type="text"
                    name="recovery_code"
                    autocomplete="one-time-code"
                    placeholder="8個のうち1つを入力"
                />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
                <button
                    type="submit"
                    form="two-factor-reset-email-form"
                    class="mt-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:border-teal-500 hover:text-teal-800"
                >
                    MFA再設定リンクをメール送信
                </button>
            </div>
        </details>

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-gradient-primary w-full sm:w-auto">
                {{ __('認証する') }}
            </button>
        </div>
    </form>

    <form id="two-factor-reset-email-form" method="POST" action="{{ route('two-factor.reset.email') }}" class="hidden">
        @csrf
    </form>
</x-guest-layout>
