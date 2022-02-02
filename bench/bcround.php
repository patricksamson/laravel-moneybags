<?php

$times = 10000000;
$precision = 10;

// Using str_repeat
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = '0.' . str_repeat('0', $precision) . '5';
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);

// Using str_pad
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = str_pad('0.', $precision, '0') . '5';
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);

// Using BCMath bcdiv
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = bcdiv('0.5', 10**$precision, $precision+1);
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);
