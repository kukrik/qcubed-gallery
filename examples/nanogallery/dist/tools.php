<?php

$test = array("position" => "overImageOnBottom","display" => false);

//print json_encode($test);

//$position = "position";
//$display = "display";

$position = "overImageOnBottom";
$display = false;

if (!is_null($val = $position)) {
    //print json_encode(['position' =>  $val]);
}

function makeJqOptions() {
    return [];
}

//if (!is_null($val = $this->ToastClass)) {$jqOptions['toastClass'] = $val;}

function makePhpOptions() {
    $jqOptions = null;

    if (!is_null($val = $position)) {$jqOptions['position'] = $val;
    }
//    if (!is_null($val = $display)) {
//        $jqOptions[] = ['display' => $val];
//    }

    return $jqOptions;
}

print json_encode([makePhpOptions()]);



