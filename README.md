# 書籍『おもしろまじめなチャットボットを作ろう』コード

LINEボットをPHPで実装。

## ボットのリスト
 - 送られたメッセージを記録するだけのボット (hear1.php, hear2.php)
 - メッセージに定型文応答するボット (hello.php)
 - メッセージをそのまま鸚鵡返しするボット (echo.php)
 - 時刻をプッシュ通知するボット (pushtime.php)
 - 用意された文章のリストからランダムに選んで応答するボット (promote/promote.php)
 - 楽天で商品検索するボット (price/pricebot.php)
 - Googleで画像検索するボット (image/image2.php)
 - 送信された位置情報を文字で返すボット (location.php)
 - 過去に送られたメッセージをランダムに選んで応答するボット (mimic/mimic.php)
 - 買い物リストを作るボット (shopping.php)

## 変更点
- フレームワーク
  - 各ボットは`process_events`を呼び出す。
  - 引数にはクロージャを渡す。これはリクエスト中のイベントごとに呼ばれる。
  - ボットごとの処理はこのクロージャで記述する。
- チャネルアクセストークンなどの秘密情報
  - ファイル `config.php` で定義。

## 楽天アプリIDの取得

https://webservice.rakuten.co.jp/ より「新規アプリ登録」に進んでアプリを作成。

アプリケーションIDをconfig.phpで`RAKUTEN_APP_ID`として定義する。

## カスタム検索エンジン(画像検索)の作成

https://cse.google.com/ より作成。最初は適当なサイトを検索対象に指定(作成時はサイト指定が必須のため)。

その後、検索エンジンの編集で設定を変更:
 - 検索するサイト: 削除
 - 画像検索: オン
 - ウェブ全体を検索: オン
 - schema.orgタイプを使用しているページを制限する: ImageObject

## Google Custom Search APIの利用
 - https://console.developers.google.com/ でプロジェクトを作成
 - Google Custom Search API を有効化
 - APIキーを作成
   - アプリケーションの制限: IPアドレス
   - キーを制限: Google Custom Search API
