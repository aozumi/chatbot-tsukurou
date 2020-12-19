<?php
require_once(dirname(__FILE__, 2) . '/tool.php');

// データを記録するファイル
define('DB_FILE', DATA_DIR . '/todo.json');

// ロックファイル
define('LOCK_FILE', DATA_DIR . '/todo.lock');

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

function prepare_user(object $db, string $user)
{
    if (!isset($db->{$user})) {
        $db->{$user} = [];
    }
}

function add_todo(object $db, string $user, int $hour, int $minute, string $title, bool $everyday)
{
    prepare_user($db, $user);
    $item = [
        'hour' => $hour % 24,
        'minute' => $minute % 60,
        'title' => $title,
        'everyday' => $everyday
    ];
    $db->{$user}[] = $item;
}

/**
 * $todosに含まれるアイテムのlast_notifiedに現在時刻のunix timeを記録する。
 */
function update_todos_last_notified(object $db, string $user, array $todos)
{
    $timestamp = time();
    if (!isset($db->{$user})) {
        return;
    }
    foreach ($db->{$user} as $todo) {
        if (in_array($todo, $todos)) {
            // $todo は object (JSONをデシリアライズするとそうなる)
            $todo->last_notified = $timestamp;
        }
    }
}

function remove_todos(object $db, string $user, array $todos)
{
    prepare_user($db, $user);
    $list = &$db->{$user};
    $db->{$user} = array_values(array_filter($list, function ($x) use ($todos) {
        return !in_array($x, $todos);
    }));
    if (empty($list)) {
        unset($db->{$user});
    }
}

function clear_todos(object $db, string $user)
{
    debug('clear', $user);
    unset($db->{$user});
}

function get_todos(object $db, string $user)
{
    debug('get todos', $user);
    prepare_user($db, $user);
    return $db->{$user};
}

function minutes_value($hour, $minute)
{
    return $hour * 60 + $minute;
}

function recent_todos(object $db, int $minutes = 5)
{
    $tm = localtime();
    $hour = $tm[2];
    $minute = $tm[1];
    foreach ($db as $user => $list) {
        $list = array_filter($list, function ($todo) use ($hour, $minute, $minutes) {
            $diff = (minutes_value($todo->hour, $todo->minute) - minutes_value($hour, $minute) + 24 * 60) % (24 * 60);
            return ($diff <= $minutes);
        });
        if (empty($list)) {
            continue;
        }
        yield [$user, array_values($list)];
    }
}

/**
 * 条件にマッチする予定のリストを返す。
 * $titleが空文字列でない場合、タイトルに$titleを含む予定のみを返す。
 */
function find_todos(object $db, string $user, int $hour, int $minute, string $title, bool $everyday) {
    return array_filter(get_todos($db, $user), function ($todo) use ($hour, $minute, $title, $everyday) {
        if ($todo->hour == $hour && $todo->minute == $minute && $todo->everyday == $everyday) {
            return ($title == "" || stripos($todo->title, $title) !== false);
        } else {
            return false;
        }
    });
}
