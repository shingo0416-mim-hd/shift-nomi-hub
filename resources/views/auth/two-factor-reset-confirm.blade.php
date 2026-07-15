<x-guest-layout>
    <div class="space-y-6 text-left">
        <div class="space-y-3">
            <p class="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-black text-teal-700 ring-1 ring-teal-200">MFA再設定</p>
            <h2 class="text-3xl font-black leading-tight text-slate-950 sm:text-4xl">2段階認証を解除しますか？</h2>
            <p class="text-sm leading-6 text-slate-600">
                {{ $user->email }} の2段階認証を解除すると、このままログインして新しい認証アプリを設定できます。
            </p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold leading-6 text-amber-900">
            解除を実行すると、このリンクは再利用できません。心当たりがない場合は解除せず、ログイン画面へ戻ってください。
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <a
                href="{{ route('login') }}"
                class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
            >
                解除しないでログインへ
            </a>

            <form method="POST" action="{{ url()->full() }}">
                @csrf
                <button type="submit" class="btn-gradient-primary min-h-12 w-full px-4 py-3">
                    MFAを解除してログイン
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
