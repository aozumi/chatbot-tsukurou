<?php
// 応答せず、送られた情報をdebug.txtに記録するだけのボット。

require_once(dirname(__FILE__) . '/tool.php');

process_events(function($event) {
    debug("event handler called", "");
});
