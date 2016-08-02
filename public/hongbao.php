<?php
function getFunc ($count, $total, $max, $min) {
    if($max<$min)
        return false;
    if($max*$count<$total)
        return false;
    if($min*$count>$total)
        return false;
    $getnum = function () use (&$count, &$total, $max, $min) {
        if($count == 1) return $total;
        if($min*$count>$total-$min) return $min;
        if($max*$count<$total+$max) return $max;
        $randmax = 2*$total/$count;
        $num = rand($min, $randmax);
        if ($num > $max) return $max;
        return $num;
    };
    $func = function () use ($getnum, &$total, &$count) {
        if($count == 0) return false;
        $num = $getnum();
        $total = $total - $num;
        $count = $count - 1;
        return $num;
    };
    return $func;
}
$count = 11;
$total = 1967;
$max = 200;
$min = 1;
$func = getFunc($count,$total,$max,$min);
$hongbao = array();
while($num = $func())
    $hongbao[] = $num;
var_dump($hongbao);
