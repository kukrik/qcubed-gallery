<?php

$paths = [
    '/galerii',
    '/galerii/sugisene-treeningulaager-joulumae-tervisekeskuses-19-10-01-11-2020',
    '/galerii/eksl-murdmaajooksu-mv-tallinnas-03-10-2020'
];

foreach ($paths as $path) {

    print $path . '<br>';
}

print '<br><br>';


$search = 'galerii';
$replace = 'pildigalerii';
$str = '';

foreach ($paths as $path) {
    $output = str_replace($search, $replace, $path);
    print $output . '<br>';
}



