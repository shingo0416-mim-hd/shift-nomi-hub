<x-guest-layout>
    <div class="w-full max-w-sm">
        <div class="mb-8 lg:hidden">
            <div class="mb-4 grid size-10 place-items-center rounded-lg bg-teal-500 text-sm font-bold text-white">SH</div>
            <p class="text-sm font-semibold text-zinc-950">ShiftHub</p>
            <p class="text-xs text-zinc-500">nomihub.jp</p>
        </div>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-zinc-950">管理画面ログイン</h1>
            <p class="mt-2 text-sm leading-6 text-zinc-500">店舗管理者アカウントでログインしてください。</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-zinc-800">メールアドレス</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    required
                    autofocus
                    class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-950 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-zinc-800">パスワード</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-950 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                >
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-zinc-600">
                    <input
                        name="remember"
                        type="checkbox"
                        class="size-4 rounded border-zinc-300 text-teal-600 focus:ring-teal-500"
                    >
                    ログイン状態を保持
                </label>
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-lg bg-teal-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-100"
            >
                ログイン
            </button>
        </form>
    </div>
</x-guest-layout>
