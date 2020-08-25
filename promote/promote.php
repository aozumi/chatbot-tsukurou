<?php
// メッセージが来たら応答としてランダムに選んだテキストを返すボット。
// 応答メッセージの候補は promote.json に記述。

require_once('../tool.php');

define('MESSAGES_FILE', dirname(__FILE__) . '/messages.json');

process_events(function($event){
    $messages = load_json(MESSAGES_FILE);
    reply($event, $messages[rand(0, count($messages) - 1)]);
});
