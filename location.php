<?php
// 送信された位置情報を文字列で返すボット

require_once(dirname(__FILE__) . '/tool.php');

process_events(function($event) {
    if (! ($event->type == 'message' && $event->message->type == 'location')) {
        return;
    }
    $title = $event->message->title;
    $address = $event->message->address;
    $latitude = $event->message->latitude;
    $longitude = $event->message->longitude;

    $text = '';
    $text .= 'タイトル: ' . $title . "\n";
    $text .= '住所: ' . $address . "\n";
    $text .= '緯度: ' . $latitude . "\n";
    $text .= '経度: ' . $longitude . "\n";

    reply($event, $text);
});
