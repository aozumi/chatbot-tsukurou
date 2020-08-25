<?php
// チャットボット共通ファイル

define('DEBUG', '/tmp/debug.txt');
if (file_exists(DEBUG)) {
    unlink(DEBUG);
}

function debug(string $title, string $text) {
    file_put_contents(DEBUG, '['.$title.']'."\n".$text."\n\n", FILE_APPEND);
}

if (file_exists(dirname(__FILE__, 1) . '/config.php')) {
    require_once(dirname(__FILE__, 1) . '/config.php');
}
// エンドポイントURL
define('REPLY_URL', 'https://api.line.me/v2/bot/message/reply');
define('PUSH_URL', 'https://api.line.me/v2/bot/message/push');

function post(string $url, array $object)
{
    $json = json_encode($object);
    debug('output', $json);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . TOKEN
    ]);

    $result = curl_exec($curl);
    debug('post result', $result);

    curl_close($curl);
}

/**
 * リプライの送信
 */
function reply(object $event, string $text)
{
    $object = [
        'replyToken' => $event->replyToken,
        'messages' => [['type'=>'text', 'text'=>$text]]
    ];
    post(REPLY_URL, $object);
}

/**
 * プッシュメッセージの送信
 */
function push(string $to, string $text)
{
    $object = [
        'to' => $to,
        'messages' => [['type'=>'text', 'text'=>$text]]
    ];
    post(PUSH_URL, $object);
}

/**
 * リクエスト中のイベントごとに$handleEventを呼び出す。
 */
function process_events(callable $handleEvent)
{
    $input = file_get_contents('php://input');
    debug('input', $input);
    if (!empty($input)) {
        $events = json_decode($input)->events;
        debug("events", count($events));
        foreach ($events as $event) {
            debug("event", "");
            call_user_func($handleEvent, $event);
        }
    }
}

/**
 * ファイルからJSONデータを読み込む。
 */
function load_json(string $file)
{
    $json = file_get_contents($file);
    return json_decode($json);
}
