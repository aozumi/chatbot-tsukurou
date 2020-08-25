<?php

// define('DEBUG', dirname(__FILE__, 2));
define('DEBUG', dirname(__FILE__, 1) . '/debug.txt');
$input = file_get_contents('php://input');
file_put_contents(DEBUG, $input);

