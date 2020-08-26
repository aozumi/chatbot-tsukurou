<?php
// チャットボット共通ファイル

if (file_exists(dirname(__FILE__, 1) . '/config.php')) {
    require_once(dirname(__FILE__, 1) . '/config.php');
}

define('DEBUG', DATA_DIR . '/' . (defined('DEBUG_FILENAME') ? DEBUG_FILENAME : 'debug.txt'));
if (file_exists(DEBUG)) {
    unlink(DEBUG);
}

function debug(string $title, string $text) {
    file_put_contents(DEBUG, '['.$title.']'."\n".$text."\n\n", FILE_APPEND);
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
 * リプライとして画像を送信
 */
function reply_image(object $event, string $originalUrl, string $previewUrl)
{
    $object = [
        'replyToken' => $event->replyToken,
        'messages' => [[
            'type' => 'image',
            'originalContentUrl' => $originalUrl,
            'previewImageUrl' => $previewUrl
        ]]
    ];

    post(REPLY_URL, $object);
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

/**
 * ファイルにJSONデータを書き込む。
 */
function save_json(string $file, $data)
{
    file_put_contents($file, json_encode($data));
}

/**
 * 自分のURLを返す。
 */
function myUrl($path = '/')
{
    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
    $port = $_SERVER['SERVER_PORT'];
    $url = ($https ? 'https://'  : 'http://');
    $url .= $_SERVER['SERVER_NAME'];
    if ($port != ($https ? '443' : '80')) {
        $url .= ':' . $port;
    }
    $url .= $path;
    return $url;
}

/**
 * ファイルのロックを獲得する。
 */
function lock_file(string $file)
{
    $fp = fopen($file, 'c');
    if (!$fp) {
        throw new Exception("failed to lock file (fopen)");
    }
    if (!flock($fp, LOCK_EX)) {
        throw new Exception("failed to lock file (flock)");
    }

    return $fp;
}

/**
 * ファイルのロックを解放する。
 */
function unlock_file($fp)
{
    if (!flock($fp, LOCK_UN)) {
        debug("failed to unlock", "flock");
    }
    fclose($fp);
}

/**
 * ファイルをロックして渡されたサンクを実行する。
 */
function with_lock(string $file, callable $thunk)
{
    $lock = lock_file($file);
    try {
        return call_user_func($thunk);
    } finally {
        unlock_file($lock);
    }
}
