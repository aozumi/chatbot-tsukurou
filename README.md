# 書籍『おもしろまじめなチャットボットを作ろう』コード

LINEボットをPHPで実装。

## ボットのリスト
 - 送られたメッセージを記録するだけのボット (hear1.php, hear2.php)
 - メッセージに定型文応答するボット (hello.php)
 - メッセージをそのまま鸚鵡返しするボット (echo.php)
 - 時刻をプッシュ通知するボット (pushtime.php)
 - 用意された文章のリストからランダムに選んで応答するボット (promote/promote.php)
 - 楽天で商品検索するボット (price/pricebot.php)

## 変更点
- フレームワーク
  - 各ボットは`process_events`を呼び出す。
  - 引数にはクロージャを渡す。これはリクエスト中のイベントごとに呼ばれる。
  - ボットごとの処理はこのクロージャで記述する。
- チャネルアクセストークン
  - ファイル `config.php` で定義。

## 楽天アプリIDの取得

https://webservice.rakuten.co.jp/ より「新規アプリ登録」に進んでアプリを作成。

アプリケーションIDをconfig.phpで`RAKUTEN_APP_ID`として定義する。