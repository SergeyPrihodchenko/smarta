<?php

$stream = fopen('data.csv', 'r');
$arr = [];
while ($row = fgetcsv($stream)) {
    $arr[] = $row;
}

$uniq = [];
foreach ($arr as $key => $value) {
    if(!in_array($value, $uniq)) {
        $uniq[$key] = $value;
    }
}

$newUniq = [];
foreach ($uniq as $k => $v) {
    $newUniq[] = $arr[$k];
}

$stream2 = fopen('uniqueData.csv', 'w');

foreach ($newUniq as $key => $value) {
    fputcsv($stream2, $value, ';');
}