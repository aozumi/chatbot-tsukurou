<?php
// 「〇〇 価格」というメッセージに対して
// 楽天で商品検索した結果を返すボット。

require_once(dirname(__FILE__, 2) . '/tool.php');
require_once(dirname(__FILE__) . '/rakuten.php');

/**
 * 商品検索の応答メッセージを生成する。
 */ 
function search_result_message(string $keyword, object $json)
{
    $msg = '「'.$keyword."」の検索結果です:\n\n";
    foreach ($json->Items as $item) {
        $msg .= mb_substr($item->Item->itemName, 0, 40)."...\n";
        $msg .= $item->Item->itemPrice."円\n";
        $msg .= $item->Item->itemUrl."\n\n";
    }
    return $msg;
}

process_events(function($event) {
    if ($event->type == 'message' && $event->message->type == 'text') {
        $text = $event->message->text;
        debug('text', $text);
        if (empty($text)) {
            return;
        }
        if (!preg_match('/価格/u', $text)) {
            return; // 「価格」がメッセージに含まれないなら無視
        }
        $keyword = preg_replace('/価格/u', '', $text);

        // 商品検索実行
        $url = rakuten_query_url($keyword);
        debug('url', $url);
        $json = load_json($url);
        debug('result', json_encode($json));

        // 結果メッセージを返す
        reply($event, search_result_message($keyword, $json));
    }
});
