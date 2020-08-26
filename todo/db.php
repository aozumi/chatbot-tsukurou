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

function add_todo(object $db, string $user, int $hour, int $minute, string $title)
{
    prepare_user($db, $user);
    $item = [
        'hour' => $hour,
        'minute' => $minute,
        'title' => $title
    ];
    $db->{$user}[] = $item;
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
