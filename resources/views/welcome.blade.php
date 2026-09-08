<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ShiftHubは、シフト希望の収集から自動割当、LINEでの確定通知までをつなぐ店舗向けシフト管理システムです。">
    <meta name="theme-color" content="#00a3af">
    <title>ShiftHub｜シフトづくりを、もっと軽やかに。</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing">
    <a class="skip-link" href="#main">本文へスキップ</a>
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="ShiftHub ホーム"><span class="brand-symbol" aria-hidden="true"><i></i><i></i><i></i><i></i></span><span>Shift<span class="brand-accent">Hub</span><small>店舗のためのシフト管理</small></span></a>
        <nav class="desktop-nav" aria-label="メインナビゲーション"><a href="#about">ShiftHubとは</a><a href="#features">主な機能</a><a href="#flow">ご利用の流れ</a><a href="#faq">よくある質問</a></nav>
        <a class="button button-small" href="{{ route('login') }}">管理画面へ <span aria-hidden="true">↗</span></a>
        <details class="mobile-nav"><summary aria-label="ナビゲーションを開く">メニュー <span aria-hidden="true">☰</span></summary><nav aria-label="モバイルナビゲーション"><a href="#about">ShiftHubとは</a><a href="#features">主な機能</a><a href="#flow">ご利用の流れ</a><a href="#faq">よくある質問</a></nav></details>
    </header>
    <main id="main">
        <section class="hero">
            <div class="hero-inner container">
                <div class="hero-copy">
                    <p class="eyebrow"><span></span> お店と、働くみんなをつなぐ。</p>
                    <h1>シフトづくりを、<br><em>もっと軽やかに。</em></h1>
                    <p class="hero-description">希望を集める。シフトを組む。みんなに届ける。<br>毎月のやりとりをひとつにまとめて、<br>お店と向き合う時間を増やしませんか。</p>
                    <div class="hero-actions"><a class="button" href="{{ route('login') }}">ShiftHubにログイン <span aria-hidden="true">→</span></a><a class="button button-outline" href="#features">機能を見てみる <span aria-hidden="true">↓</span></a></div>
                    <p class="hero-note">シフト希望収集 <b>／</b> 自動割当 <b>／</b> LINE通知</p>
                </div>
                <div class="hero-visual" aria-label="シフト管理画面とスマートフォン通知のイメージ">
                    <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
                    <div class="visual-label">毎月のシフトが、ひとつにつながる。</div>
                    <div class="screen">
                        <div class="screen-bar"><strong><span class="screen-mark">▦</span> ShiftHub</strong><span>シフト管理 <i></i></span></div>
                        <div class="screen-body">
                            <div class="screen-side"><span class="side-dot"></span><span class="selected">▦</span><span>♧</span><span>⌂</span></div>
                            <div class="screen-content"><div class="screen-title"><div><small>SHIFT SCHEDULE</small><h2>9月のシフト</h2></div><span class="status">確定済み</span></div>
                                <div class="screen-toolbar"><span>本店 <span aria-hidden="true">⌄</span></span><span>2026年 9月</span></div>
                                <table class="demo-table"><caption class="sr-only">サンプルの週間シフト表</caption><thead><tr><th scope="col">キャスト</th>@foreach(['7 月','8 火','9 水','10 木','11 金','12 土','13 日'] as $day)<th scope="col">{{ $day }}</th>@endforeach</tr></thead><tbody>
                                @foreach(['佐藤','鈴木','高橋','田中','伊藤','渡辺'] as $index => $name)
                                    <tr><th scope="row"><span class="avatar avatar-{{ $index % 3 }}">{{ mb_substr($name, 0, 1) }}</span>{{ $name }}</th>@for($day = 0; $day < 7; $day++)<td>@if(($index + $day) % 4 === 0)<span class="shift-off">休</span>@else<span class="shift shift-{{ ($index + $day) % 3 }}">{{ $index % 2 === 0 ? '18–23' : '19–24' }}</span>@endif</td>@endfor</tr>
                                @endforeach
                                </tbody></table><div class="screen-footer"><span><i></i> シフトの割当状況をひと目で確認</span><span>6名</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="phone"><div class="phone-camera"></div><div class="phone-top"><span>9:41</span><span>● ▰</span></div><div class="phone-header">‹ <strong>お店からのお知らせ</strong></div><div class="phone-chat"><span class="chat-avatar">SH</span><div><small>ShiftHub</small><div class="chat-bubble">シフトが確定しました！<br><br>9/7　18:00–23:00<br>9/9　18:00–23:00<br>9/10　18:00–23:00<br><br>よろしくお願いします。</div><small class="chat-time">9:41</small></div></div><div class="phone-bottom">＋ <span>メッセージを入力</span> ◉</div></div>
                    <div class="floating-note"><span>✓</span><div>確定したら、LINEへ。<small>連絡もスムーズに</small></div></div>
                    <p class="visual-disclaimer">※画面・通知はサービスの利用イメージです。</p>
                </div>
            </div>
        </section>
        <div class="benefit-strip container"><p>シフト管理の「手間」を、<br><strong>ひとつずつ、シンプルに。</strong></p><div><span>01</span> 希望をまとめて収集</div><div><span>02</span> 条件に合わせて自動割当</div><div><span>03</span> 確定シフトをLINE通知</div></div>
        <section class="section about container" id="about">
            <div class="section-heading"><p class="eyebrow">ABOUT SHIFTHUB</p><h2>人とお店に、<br class="mobile-break">ちょうどいいシフト管理。</h2><p>バラバラに届く希望、何度も見直すシフト表。<br>ShiftHubは、そんな店舗運営の日常を支えるサービスです。</p></div>
            <div class="about-grid"><article><span class="line-icon" aria-hidden="true">▤</span><h3>情報がまとまる</h3><p>キャストの情報も、店舗のシフトも。<br>ひとつの管理画面で整理できます。</p></article><article><span class="line-icon" aria-hidden="true">✧</span><h3>作成がスムーズに</h3><p>提出された希望と必要人数をもとに、<br>日々の割当作業をサポートします。</p></article><article><span class="line-icon" aria-hidden="true">↗</span><h3>連絡がつながる</h3><p>提出のリマインドから確定通知まで。<br>いつものLINEでやりとりできます。</p></article></div>
        </section>
        <section class="section features" id="features"><div class="container">
            <div class="section-heading"><p class="eyebrow">FEATURES</p><h2>毎月のシフト業務を、<br class="mobile-break">これひとつで。</h2><p>集める・つくる・届ける。必要な機能を、使いやすく。</p></div>
            <div class="feature-grid">
                <article class="feature-card"><div class="feature-art art-collect" aria-hidden="true"><div class="mini-form"><div class="mini-heading">シフト希望を提出</div><div>9/7（月） <b>18:00 – 23:00</b></div><div>9/8（火） <b>お休み</b></div><div>9/9（水） <b>18:00 – 23:00</b></div><span class="mini-submit">希望を提出する ✓</span></div><span class="art-badge">LINE</span></div><div class="feature-text"><p class="feature-number">01 <span>COLLECT</span></p><h3>希望の収集を、手軽に。</h3><p>キャストがスマートフォンからシフト希望を提出。提出状況の確認やリマインドで、集計の手間を減らします。</p><span class="feature-tag">希望提出・リマインド</span></div></article>
                <article class="feature-card"><div class="feature-art art-schedule" aria-hidden="true"><div class="mini-schedule"><div class="mini-heading">シフト自動割当 <span>✧</span></div><div class="mini-grid">@for($i = 0; $i < 28; $i++)<span class="mini-cell cell-{{ $i % 5 }}"></span>@endfor</div><div class="mini-complete">✓ 希望と必要人数をもとに作成</div></div></div><div class="feature-text"><p class="feature-number">02 <span>CREATE</span></p><h3>割当作業を、効率よく。</h3><p>シフト希望や勤務の重複、割当回数を考慮して自動作成。不足している枠を確認し、管理画面から調整できます。</p><span class="feature-tag">自動割当・不足状況の確認</span></div></article>
                <article class="feature-card"><div class="feature-art art-notify" aria-hidden="true"><div class="mini-notification"><span class="notification-icon">✓</span><div><strong>シフトが確定しました</strong><p>今月の勤務予定をお知らせします。</p><small>ShiftHub · たった今</small></div></div><div class="notification-caption">いつものLINEに、届く。</div></div><div class="feature-text"><p class="feature-number">03 <span>SHARE</span></p><h3>決まったシフトを、届ける。</h3><p>確定した勤務予定をキャストへLINEで通知。管理画面からの公開と連絡をつなげ、共有をスムーズにします。</p><span class="feature-tag">シフト公開・LINE通知</span></div></article>
            </div><p class="section-note">※LINE機能の利用には、店舗ごとのLINE連携設定が必要です。</p>
        </div></section>
        <section class="section container" id="flow"><div class="section-heading"><p class="eyebrow">HOW IT WORKS</p><h2>いつもの業務を、<br class="mobile-break">新しい流れに。</h2></div><ol class="flow-list"><li><span>STEP <b>01</b></span><h3>店舗・キャストを登録</h3><p>店舗情報とキャスト情報を整え、LINE連携を設定します。</p></li><li><span>STEP <b>02</b></span><h3>シフト希望を集める</h3><p>募集する期間と提出期限を設定。キャストから希望を受け付けます。</p></li><li><span>STEP <b>03</b></span><h3>シフトを作成・共有</h3><p>割当状況を確認して調整。確定したシフトをLINEで届けます。</p></li></ol></section>
        <section class="section faq-section" id="faq"><div class="faq-layout container"><div class="section-heading"><p class="eyebrow">FAQ</p><h2>よくある質問</h2><p>ご利用前に、知っておきたいこと。</p></div><div class="faq-list">
            <details><summary><span>Q</span> スマートフォンから希望を提出できますか？</summary><p>はい。店舗から案内されたLINE連携の登録手順を完了すると、スマートフォンからシフト希望を提出できます。</p></details>
            <details><summary><span>Q</span> 複数の店舗を管理できますか？</summary><p>はい。管理画面で店舗を登録し、店舗ごとのキャストやシフトを管理できます。</p></details>
            <details><summary><span>Q</span> 自動作成したシフトは調整できますか？</summary><p>はい。管理画面からシフトの割当を確認・変更できます。必要人数に満たない枠も確認しながら調整できます。</p></details>
            <details><summary><span>Q</span> 管理画面にログインするには？</summary><p>発行済みの管理者アカウントでログインしてください。メールアドレスとパスワードに加え、認証アプリを使った2段階認証に対応しています。アカウントについては店舗の管理者にご確認ください。</p></details>
        </div></div></section>
        <section class="closing"><div class="container"><p class="eyebrow">LET’S START</p><h2>シフトの先にある、<br>お店の時間をもっと豊かに。</h2><p>毎月のシフト管理を、ShiftHubではじめましょう。</p><a class="button button-white" href="{{ route('login') }}">管理画面にログイン <span aria-hidden="true">→</span></a><small>ご利用には管理者アカウントが必要です。</small></div></section>
    </main>
    <footer class="site-footer container"><a class="brand" href="{{ route('home') }}"><span class="brand-symbol" aria-hidden="true"><i></i><i></i><i></i><i></i></span><span>Shift<span class="brand-accent">Hub</span></span></a><p>お店と、働くみんなをつなぐ。</p><small>© {{ date('Y') }} ShiftHub</small><a href="#main" class="back-top" aria-label="ページの先頭へ">↑</a></footer>
</body>
</html>
