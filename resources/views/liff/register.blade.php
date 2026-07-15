<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">

        <title>スタッフ登録 - {{ config('app.name', 'ShiftHub') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-5 py-8">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200">
                <div class="mb-5 border-b border-slate-200 pb-4">
                    <p class="text-xs font-black text-teal-700">ShiftHub Mini App</p>
                    <h1 class="mt-1 text-2xl font-black text-slate-950">スタッフ登録</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $member->displayName() }} さんとしてLINEアカウントを登録します。</p>
                </div>

                <dl class="space-y-3 rounded-xl bg-slate-50 p-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="font-bold text-slate-500">所属</dt>
                        <dd class="text-right font-black text-slate-900">{{ $member->tenant?->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="font-bold text-slate-500">店舗</dt>
                        <dd class="text-right font-black text-slate-900">{{ $member->store?->name ?? '未割り当て' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="font-bold text-slate-500">状態</dt>
                        <dd class="text-right font-black {{ $member->is_linked ? 'text-emerald-700' : 'text-amber-700' }}">{{ $member->is_linked ? '登録済み' : '未登録' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-bold text-slate-700" data-liff-status>登録準備中です。</p>
                    <p class="mt-2 hidden text-sm leading-6 text-red-700" data-liff-error></p>
                </div>

                <button type="button" class="mt-5 w-full rounded-xl bg-teal-700 px-4 py-3 text-sm font-black text-white transition hover:bg-teal-800" data-action="register-liff-member">
                    LINEで登録する
                </button>
            </section>
        </main>

        <script>
            (() => {
                const registrationToken = {{ Illuminate\Support\Js::from($registrationToken) }};
                const liffId = {{ Illuminate\Support\Js::from($liffId) }};
                const member = {{ Illuminate\Support\Js::from([
                    'tenant_id' => $member->tenant_id,
                    'store_id' => $member->store_id,
                    'name' => $member->displayName(),
                ]) }};
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const status = document.querySelector('[data-liff-status]');
                const error = document.querySelector('[data-liff-error]');

                const setStatus = (message) => {
                    status.textContent = message;
                };

                const setError = (message) => {
                    error.textContent = message;
                    error.classList.toggle('hidden', !message);
                };

                const api = async (path, payload) => {
                    const response = await fetch(path, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        const message = data.message || Object.values(data.errors || {}).flat().join('\n') || '登録に失敗しました。';
                        throw new Error(message);
                    }

                    return data;
                };

                const register = async () => {
                    setError('');
                    try {
                        let profile = null;
                        if (liffId && window.liff) {
                            setStatus('LINEミニアプリを確認しています。');
                            await liff.init({ liffId });
                            if (!liff.isLoggedIn()) {
                                liff.login({ redirectUri: window.location.href });
                                return;
                            }
                            profile = await liff.getProfile();
                        }

                        if (!profile) {
                            setStatus('開発用登録モードです。テナントのLIFF IDを設定するとLINEプロフィールで登録します。');
                            profile = {
                                userId: `dev-${registrationToken.slice(0, 16)}`,
                                displayName: member.name || 'LINE User',
                                pictureUrl: null,
                            };
                        }

                        setStatus('スタッフ情報を登録しています。');
                        await api('/api/liff/auth/login', {
                            tenant_id: member.tenant_id,
                            store_id: member.store_id,
                            registration_token: registrationToken,
                            line_user_id: profile.userId,
                            display_name: profile.displayName,
                            picture_url: profile.pictureUrl,
                        });

                        setStatus('登録が完了しました。');
                    } catch (caughtError) {
                        setStatus('登録できませんでした。');
                        setError(caughtError.message);
                    }
                };

                document.querySelector('[data-action="register-liff-member"]')?.addEventListener('click', register);
                register();
            })();
        </script>
    </body>
</html>
