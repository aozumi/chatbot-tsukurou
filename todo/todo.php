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

/**
 * 指定された時刻の予定を削除する。
 * $title が与えられた場合はそれにマッチするものを削除する。
 */
function handle_remove_todo(object $event, string $user, int $hour, int $minute, string $title)
{
    $to_remove = [];
    with_lock(LOCK_FILE, function () use (&$to_remove, $user, $hour, $minute, $title) {
        $db = load_db();
        $to_remove = find_todos($db, $user, $hour, $minute, $title);
        if (!empty($to_remove)) {
            remove_todos($db, $user, $to_remove);
            save_db($db);
        }
    });
    if (empty($to_remove)) {
        reply($event, "該当する予定はありませんでした。");
        return;
    }
    $n = count($to_remove);
    $msg = "{$n}件の予定を削除しました:";
    foreach ($to_remove as $todo) {
        $msg .= "\n" . format_todo($todo);
    }
    reply($event, $msg);
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

function handle_help(object $event)
{
    $help = (
        "予定リスト\n  予定の一覧を表示します\n"
        . "XX:XX 予定内容\n  予定を登録します\n"
        . "予定クリア\n  予定を全て消去します\n"
        . "キャンセル XX:XX [キー]\n  予定を削除します\n"
        . "ヘルプ\n  このメッセージを表示します"
    );
    reply($event, $help);
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
    if (preg_match('/^([0-9]+):([0-9]+) *(.+)/u', $text, $matches)) {
        [, $hour, $minute, $title] = $matches;
        debug('add todo', '[' . $hour . ':' . $minute . '] ' . $title);
        handle_add_todo($event, $user, (int)$hour, (int)$minute, trim($title));
    } else if (preg_match('/^予定(リスト.*)?クリア/u', $text)) {
        debug('clear list', '');
        handle_clear_list($event, $user);
    } else if (preg_match('/^キャンセル ([0-9]+):([0-9]+) *(.*)/u', $text, $matches)) {
        [, $hour, $minute, $title] = $matches;
        debug('remove todo', "{$hour}:{$minute} {$title}");
        handle_remove_todo($event, $user, (int)$hour, (int)$minute, trim($title));
    } else if (preg_match('/予定リスト/u', $text)) {
        debug('show list', '');
        handle_show_list($event, $user);
    } else if (preg_match('/(ヘルプ|help)/u', $text)) {
        debug('show help', '');
        handle_help($event);
    }
});
