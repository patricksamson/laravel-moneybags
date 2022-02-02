<?php

$times = 100000000;
$precision = 10;
$amount = '123456789.12345';

// Using str_pos
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = strpos($amount, '.') !== false;
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);

// Using str_contains
$start_time = microtime(true);
$i = 0;
while ($i < $times) {
    $temp = str_contains($amount, '.');
    $i++;
}
$end_time = microtime(true);
var_dump($end_time - $start_time);
