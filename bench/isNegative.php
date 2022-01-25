<?php

$times = 100000000;
$precision = 10;
$amount = '12.345678';

// Using string concatenation
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = $amount[0] != '-';
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);

// Using str_starts_with
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = str_starts_with($amount, '-');
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);

// Using strpos
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = strpos($amount, '-');
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);

// Using BCMath bccomp
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = bccomp($amount, '0', $precision) < 0;
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);
