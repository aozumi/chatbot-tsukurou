# 書籍『おもしろまじめなチャットボットを作ろう』

## 変更点
- フレームワーク
  - 各ボットは`process_events`を呼び出す。
  - 引数にはクロージャを渡す。これはリクエスト中のイベントごとに呼ばれる。
  - ボットごとの処理はこのクロージャで記述する。
- チャネルアクセストークン
  - ファイル `config.php` で定義。