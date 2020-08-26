<?php
// 全てのメッセージに Hello! と返すボット。

require_once(dirname(__FILE__, 2) . '/tool.php');
require_once(dirname(__FILE__) . '/google.php');

function force_https(string $url)
{
    return preg_replace('/^http:/', 'https:', $url);
}

process_events(function($event) {
    if (! ($event->type == 'message' && $event->message->type == 'text' && isset($event->message->text))) {
        return;
    }
    $text = $event->message->text;
    if (!preg_match('/の画像$/u', $text)) {
        return;
    }
    $keyword = preg_replace('/の画像$/u', '', $text);

    // 検索実行
    $url = google_search_image_url($keyword);
    debug('search url', $url);
    $json = load_json($url);
    $items = $json->items;

    // 結果からランダムに選ぶ
    $item = $items[rand(0, count($items)-1)];
    $imageUrl = force_https($item->link);
    $previewUrl = force_https($item->image->thumbnailLink);
    debug('original image', $imageUrl);
    debug('preview image', $previewUrl);

    reply_image($event, $imageUrl, $previewUrl);
});
