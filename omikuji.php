<?php
// おみくじボット

require_once(dirname(__FILE__) . '/tool.php');

function show_button(object $event)
{
    $message = [
        'type' => 'template',
        'altText' => 'ボタンが表示できません',
        'template' => [
            'type' => 'buttons',
            'text' => 'どのおみくじをひきますか',
            'actions' => [
                [ 
                    'type' => 'postback',
                    'label' => '上段',
                    'data' => '0'
                ],
                [ 
                    'type' => 'postback',
                    'label' => '中段',
                    'data' => '1'
                ],
                [ 
                    'type' => 'postback',
                    'label' => '下段',
                    'data' => '2'
                ],
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
    $fortune = ['大吉', '小吉', '凶'];
    $value = (int)$event->postback->data;
    $n = rand(0, count($fortune)-1) + $value;
    $result = $fortune[$n % count($fortune)];

    $text = 'おみくじの結果は「' . $result . '」でした';
    reply($event, $text);
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
