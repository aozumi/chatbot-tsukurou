<?php
// 予定リストを作るボット。
// - 「XX:XX 〇〇」 → 予定リストに追加
// - 「予定リスト」→予定リストを表示
// - 「予定クリア」→予定リストを削除

require_once(dirname(__FILE__, 2) . '/tool.php');
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/utils.php');

date_default_timezone_set('Asia/Tokyo');

function handle_add_todo(object $event, string $user, int $hour, int $minute, string $title)
{
    with_lock(LOCK_FILE, function () use ($user, $hour, $minute, $title) {
        $db = load_db();
        add_todo($db, $user, $hour, $minute, $title);
        save_db($db);
    });

    reply($event, '予定を追加しました。');
}

function handle_show_list(object $event, string $user)
{
    $list = with_lock(LOCK_FILE, function () use ($user) {
        $db = load_db();
        return get_todos($db, $user);
    });

    if (empty($list)) {
        reply($event, '予定はありません。');
        return;
    }

    $text = '予定は次の' . count($list) . '件です:';
    $list = sort_todos($list);
    foreach ($list as $item) {
        $text .= "\n " . format_todo($item);
    }
    reply($event, $text);
}

function handle_clear_list(object $event, string $user)
{
    with_lock(LOCK_FILE, function () use ($user) {
        $db = load_db();
        clear_todos($db, $user);
        save_db($db);
    });
    reply($event, '予定を全て削除しました。');
}

process_events(function($event) {
    if (!($event->type == 'message' && $event->message->type == 'text')) {
        return;
    }

    $text = $event->message->text;
    debug('text', $text);
    if (empty($text)) {
        return;
    }
    $user = $event->source->userId;
    if (empty($user)) {
        return; // ユーザ不明
    }

    $matches = [];
    if (preg_match('/([0-9]+):([0-9]+) *(.+)/u', $text, $matches)) {
        [, $hour, $minute, $title] = $matches;
        debug('add todo', '[' . $hour . ':' . $minute . '] ' . $title);
        handle_add_todo($event, $user, (int)$hour, (int)$minute, trim($title));
    } else if (preg_match('/予定(リスト.*)?クリア/u', $text)) {
        debug('clear list', '');
        handle_clear_list($event, $user);
    } else if (preg_match('/予定リスト/u', $text)) {
        debug('show list', '');
        handle_show_list($event, $user);
    }
});
