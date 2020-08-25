<?php
// 全てのメッセージに Hello! と返すボット。

require_once('tool.php');

process_events(function($event) {
    reply($event, 'Hello!');
});
