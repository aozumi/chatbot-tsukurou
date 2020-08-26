<?php
// これまでに送信されたメッセージの中から
// ランダムに選んで応答するボット。

require_once(dirname(__FILE__, 2) . '/tool.php');

// 記憶する発言数の上限
define('MESSAGES_LIMIT', 100);

// 発言を記録するファイル
define('MESSAGES_FILE', '/tmp/messages.txt');

// ロックファイル
define('LOCK_FILE', '/tmp/mimic.lock');

function add_message(object $messages, string $user, string $newMessage)
{
    if (!isset($messages->{$user})) { // array_key_exists($user, $messages)) {
        $messages->{$user} = (object) [];
    }
    $list = &$messages->{$user};
    debug('number of recorded messages for ' . $user, count($list));

    $list = array_filter($list, function($x) use ($newMessage) {
        return $x != $newMessage;
    });
    $list = array_values($list); // インデックスの再構築
    $list[] = $newMessage;
    if (count($list) > MESSAGES_LIMIT) {
        array_shift($list);
    }
}

function pick_message(object $messages, string $user)
{
    $list = &$messages->{$user};
    return $list[rand(0, count($list)-1)];
}

process_events(function($event) {
    if ($event->type == 'message' && $event->message->type == 'text') {
        $text = $event->message->text;
        debug('text', $text);
        if (empty($text)) {
            return;
        }
        $user = $event->source->userId;
        if (empty($user)) {
            return; // ユーザ不明
        }

        $lock = lock_file(LOCK_FILE);
        $response = '';
        try {
            if (file_exists(MESSAGES_FILE)) {
                $messages = load_json(MESSAGES_FILE);
            } else {
                $messages = [];
            }

            add_message($messages, $user, $text);
            $response = pick_message($messages, $user);
            
            save_json(MESSAGES_FILE, $messages);
        } finally {
            unlock_file($lock);
        }

        reply($event, $response);
    }
});
