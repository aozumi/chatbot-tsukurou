<?php
// 現在時刻を通知するボット。

require_once('tool.php');

date_default_timezone_set('Asia/Tokyo');
push(USERID_FOR_TEST, date('ただいまG時i分s秒です。'));
