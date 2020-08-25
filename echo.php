<?php
// テキストメッセージには送られたメッセージをそのまま返し、
// それ以外には Hello! と返すボット。

require_once('tool.php');

process_events(function($event) {
    if ($event->type == 'message' && $event->message->type == 'text') {
        reply($event, $event->message->text);
    } else {
        reply($event, 'Hello!');
    }
});
