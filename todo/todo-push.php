<?php
// 直近の予定をプッシュ通知するプログラム

// このプログラムはホスト上で実行されることを想定したものなので
// HTTPリクエストにより実行された場合は即座に終了する。
if (isset($_SERVER['REQUEST_URI'])) {
    die();
}

define('DEBUG_FILENAME', 'debug-push.txt');
require_once(dirname(__FILE__, 2) . '/tool.php');
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/utils.php');

// 現在時刻より何分先までの予定を通知するか。
// cronの呼び出し間隔より十分に長い必要がある
// (cronの呼び出しは遅延しうる)。
define('RECENT_MINUTES', 10);

date_default_timezone_set('Asia/Tokyo');

debug('todo-push', 'start');
debug('now', strftime('%R'));
debug('RECENT_MINUTES', RECENT_MINUTES);

$db = with_lock(LOCK_FILE, function () {
    return load_db();
});
$remove = []; // 通知後に削除するアイテム(ユーザごと)
foreach (recent_todos($db, RECENT_MINUTES) as $index => [$user, $list]) {
    debug('user with todos', $user);
    debug('number of items', count($list));

    $list = sort_todos($list);

    // 通知メッセージ作成
    $text = '予定の時刻です:';
    foreach ($list as $todo) {
        $text .= "\n" . format_todo($todo);
    }

    // 削除するアイテム
    $to_remove = array_filter($list, function ($todo) {
        return ! $todo->everyday;
    });

    // 通知の送信に成功したら削除対象を登録
    try {
        debug('push', $text);
        push($user, $text);
        $remove[$user] = $to_remove;
    } catch (Exception $e) {
        debug('exception', $e->getMessage());
    }
}

// 不要になったアイテムを削除する
if (!empty($remove)) {
    with_lock(LOCK_FILE, function () use ($remove) {
        $db = load_db();
        foreach ($remove as $user => $list) {
            debug('remove items', $user . "\n" . count($list));
            remove_todos($db, $user, $list);
        }
        save_db($db);
    });
}
