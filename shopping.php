<?php
// 買い物リストを作るボット。
// - 「〇〇買う」 → 買い物リストに追加
// - 「〇〇買った」「〇〇やめた」→ 買い物リストから削除
// - 「買い物リスト」→買い物リストを表示
// - 「買い物クリア」→買い物リストを削除

require_once(dirname(__FILE__) . '/tool.php');

// データを記録するファイル
define('DB_FILE', DATA_DIR . '/shopping.json');

// ロックファイル
define('LOCK_FILE', DATA_DIR . '/shopping.lock');

function load_db()
{
    if (file_exists(DB_FILE)) {
        return load_json(DB_FILE);
    } else {
        return (object)[];
    }
}

function save_db(object $db)
{
    save_json(DB_FILE, $db);
}

function prepare_shopping_list(object $db, string $user)
{
    if (!isset($db->{$user})) {
        $db->{$user} = [];
    }
}

function clear_shopping_list(object $db, string $user)
{
    debug('clear shopping list', $user);
    unset($db->{$user});
}

function get_shopping_list(object $db, string $user)
{
    debug('get shopping list', $user);
    prepare_shopping_list($db, $user);
    return $db->{$user};
}

/**
 * 買い物リストにまだ入っていなければアイテムを追加する。
 * 新たに追加したらtrueを返す。
 */
function add_shopping_item(object $db, string $user, string $item)
{
    debug('add item', $item);
    prepare_shopping_list($db, $user);
    if (!in_array($item, $db->{$user})) {
        $db->{$user}[] = $item;
        return true;
    } else {
        return false;
    }
}

/**
 * 買い物リストからアイテムを削除する。
 */
function remove_shopping_item(object $db, string $user, string $item)
{
    debug('remove item', $item);
    prepare_shopping_list($db, $user);
    $list = &$db->{$user};
    $list = array_values(array_filter($list, function ($x) use ($item) { return $x != $item; }));
}

function handle_add_item(object $event, string $user, string $item)
{
    $added = with_lock(LOCK_FILE, function () use ($user, $item) {
        $db = load_db();
        $added = add_shopping_item($db, $user, $item);
        if ($added) {
            save_db($db);
        }
        return $added;
    });

    reply($event, ($added
                   ? ($item . 'を買い物リストに追加しました。')
                   : ($item . 'は既に追加済みです。')));
}

function handle_remove_item(object $event, string $user, string $item)
{
    with_lock(LOCK_FILE, function () use ($user, $item) {
        $db = load_db();
        remove_shopping_item($db, $user, $item);
        save_db($db);
    });
    reply($event, $item . 'を買い物リストから削除しました。');
}

function handle_show_list(object $event, string $user)
{
    $list = with_lock(LOCK_FILE, function () use ($user) {
        $db = load_db();
        return get_shopping_list($db, $user);
    });

    if (empty($list)) {
        reply($event, '買い物リストは空です。');
        return;
    }
    $text = '買い物リストは次の' . count($list) . '件です:';
    foreach ($list as $item) {
        $text .= "\n - " . $item;
    }
    reply($event, $text);
}

function handle_clear_list(object $event, string $user)
{
    with_lock(LOCK_FILE, function () use ($user) {
        $db = load_db();
        clear_shopping_list($db, $user);
        save_db($db);
    });
    reply($event, '買い物リストをクリアしました。');
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
    if (preg_match('/(.+)買う/u', $text, $matches)) {
        $item = trim($matches[1]);
        debug('add item', $item);
        handle_add_item($event, $user, $item);
    } else if (preg_match('/(.+)(買った|やめた|いらない)/u', $text, $matches)) {
        $item = trim($matches[1]);
        debug('remove item', $item);
        handle_remove_item($event, $user, $item);
    } else if (preg_match('/買い物(リスト.*)?クリア/u', $text)) {
        debug('clear list', '');
        handle_clear_list($event, $user);
    } else if (preg_match('/買い物リスト/u', $text)) {
        debug('show list', '');
        handle_show_list($event, $user);
    }
});
