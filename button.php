<?php
// ボタンテンプレートの利用例

require_once(dirname(__FILE__) . '/tool.php');

function show_button(object $event)
{
    $message = [
        'type' => 'template',
        'altText' => 'ボタンが表示できません',
        'template' => [
            'type' => 'buttons',
            'text' => 'ボタンの使用例です',
            'actions' => [
                [ 
                    'type' => 'postback',
                    'label' => 'クマ (Postback)',
                    'data' => 'bear'
                ],
                [
                    'type' => 'message',
                    'label' => 'ペンギン (Message)',
                    'text' => 'penguin',
                ],
                [
                    'type' => 'uri',
                    'label' => 'ひぐぺん工房 (URI)',
                    'uri' => 'http://cgi1.plala.or.jp/~higpen/',
                ]
            ]
        ]
    ];
    $object = [
        'replyToken' => $event->replyToken,
        'messages' => [$message]
    ];
    reply_object($event, $object);
}

function handle_button_postback(object $event)
{
    reply($event, $event->postback->data);
}

process_events(function($event) {
    if ($event->type == 'message') {
        // 何らかのメッセージがあれば、応答としてボタンを表示
        show_button($event);
    } else if ($event->type == 'postback') {
        // ボタンが押されたときのpostbackアクション
        handle_button_postback($event);
    }
});
