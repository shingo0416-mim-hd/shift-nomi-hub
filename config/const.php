<?php
// 環境事に変わることの多い定数をこちらに記載する
// 必ず固定値となり、環境ごとに変わらないものはapp/Constants以下に記載する。
if ('http://localhost' === getenv ( "APP_URL" ) or '127.0.0.1' === getenv ( "APP_URL" )) {
  // localhostはlaravelの設定で8000ポート使う。またapiアクセスのため、2つの仮想サーバーを立ち上げる必要がある
  $thispage_url_host = getenv ( "APP_URL" ) . ':8000';
  $thispage_api_host = getenv ( "APP_URL" ) . ':8001';
} else {
  $thispage_api_host = $thispage_url_host = getenv ( "APP_URL" );
}
// HTTP_HOSTが設定されているのであれば、そちらからも設定を許可する
if(empty($thispage_url_host) && isset($_SERVER['HTTP_HOST'])){
  $thispage_api_host = $thispage_url_host ='https://'
    . $_SERVER['HTTP_HOST'];
}

// trailingSlash対策
$thispage_url_host = rtrim($thispage_url_host,'/');
$thispage_api_host = rtrim($thispage_api_host,'/');

return [
  /**
   * viewでも利用可能な固定値のリスト
   */
  "dspDatas" => [
    // このサイトのURLベース (aaa.com/まで)
    'BASE_URL' => $thispage_url_host . '/',
    'API_URL' => $thispage_api_host . '/api/',
    // リソースフォルダ関連
    'CSS' => $thispage_url_host.'/css/',
    'JS' => $thispage_url_host . '/js/',
    'IMG' => $thispage_url_host . '/img/',
    // LINE公式のトークタイムラインURL
    'LINE_TIMELINE_URL'=> 'https://line.me/R/ti/p/'.getenv ( "LINE_OFFICIAL_ACCOUNTID" ),
  ],
];