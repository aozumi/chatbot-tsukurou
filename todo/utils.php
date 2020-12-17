<?php

/**
 * 予定リストをソートする。
 * 現在時刻を基準にして、次に来るのが早い順に並べる。
 */
function sort_todos(array $list)
{
    $tm = localtime();
    $hour = $tm[2]; // hour
    $minute = $tm[1]; // minute
    usort($list, function ($a, $b) use ($hour, $minute){
        $a_time = (($a->hour - $hour + 24) * 60 + $a->minute - $minute) % (24 * 60);
        $b_time = (($b->hour - $hour + 24) * 60 + $b->minute - $minute) % (24 * 60);
        if ($a_time != $b_time) {
            return $a_time - $b_time;
        } else {
            return strcmp($a->title, $b->title);
        }
    });
    return $list;
}

function format_todo(object $todo)
{
    $mark = ($todo->everyday ? "*" : "");
    return sprintf('%02d:%02d%s %s', $todo->hour, $todo->minute, $mark, $todo->title);
}
