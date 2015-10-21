<?php

$csv = $_POST['csvFile'];
$action = $_POST['action'];
$proto = $_POST['maxProto'];

switch($action) {
    case 'prototype':
        include_once('../assets/parseCSS.php');
        prototype($csv, $proto);
        break;
    case 'rta':
        rta($csv);
        break;
    case 'mostProto':
        getMax($csv);
        break;
    case 'popStudents':
        popStudents($csv);
        break;
    default:
        echo 'No action chosen';
        break;
}

function buildArray($csv){
    $output = [];

    $sData = str_getcsv($csv, "\n", $enclosure = '"');

    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');

    foreach ($sData as $aRow) {

        if($aRow[2] !== '') {
            if (strpos($aRow[9], 'score')) {
                if (isset($output[$aRow[2]]['score'])) {
                    $output[$aRow[2]]['score'] += $aRow[10];
                    $output[$aRow[2]]['maxScore'] += $aRow[11];
                } else {
                    $output[$aRow[2]]['score'] = $aRow[10];
                    $output[$aRow[2]]['maxScore'] = $aRow[11];
                }
                $output[$aRow[2]]['sAvg'] = $output[$aRow[2]]['score'] / $output[$aRow[2]]['maxScore'];
            } else {
                if (isset($output[$aRow[2]]['ot'])) {
                    $output[$aRow[2]]['ot'] += (int)$aRow[10];
                    $output[$aRow[2]]['maxOt'] += (int)$aRow[11];
                } else {
                    $output[$aRow[2]]['ot'] = (int)$aRow[10];
                    $output[$aRow[2]]['maxOt'] = (int)$aRow[11];
                }

                if($output[$aRow[2]]['maxOt'] > 0) {
                    $output[$aRow[2]]['otAvg'] = $output[$aRow[2]]['ot'] / $output[$aRow[2]]['maxOt'];
                }
            }
        }
    }
    return $output;
}


function prototype($csv, $proto){
    $output = buildArray($csv);

    $str = "<table><tr><th>Name</th><th>Score</th><th>Personal Possible Score</th><th>On Time</th><th>Possible On Time</th><th>Overall Possible</th><th>Personal Snapshot</th><th>Overall Snapshot</th></tr>";

    foreach ($output as $k=>$v) {
        if ($k !== 'Student Name') {

            $avg = round(($v['score'] + $v['ot']) / ($v['maxScore'] + $v['maxOt']), 2)*100;
            $avg2 = round(($v['score'] + $v['ot']) / ($proto + ($proto/2)), 2)*100;
            $str .= "<tr><td>$k</td><td>" . $v['score'] . "</td><td>" . $v['maxScore'] . "</td><td>" . $v['ot'] . "</td><td>" . $v['maxOt'] .
                "</td><td>$proto</td><td>$avg%</td><td>$avg2%</td></tr>";
        }
    }
        $str .= '</table><h3><a href="../index.php">Home</a></h3>';

        echo $str;
}

function rta($csv) {
    $output = [];

    $setDates = false;

    $sDate = strtotime($_POST['start-date']);
    $eDate = strtotime($_POST['end-date']);

    if($sDate > 0){
        if($eDate == 0){
            $eDate = time();
        }
        $setDates = true;
    }

    echo '<br>'.$setDates;

    $sData = str_getcsv($csv, "\n", $enclosure = '"');

    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');

    foreach($sData as $k=>$v){

        $date = explode(' ', $v[0])[0];
        $name = $v[4];

        if (strpos($v[9], 'score')) {
            if (!isset($output[$name][$date])) {
                if($setDates){
                    $ts = strtotime($date);
                    if($ts >= $sDate && $ts<= $eDate){
                        $output[$name][$date] = 1;
                    }
                }else {
                    $output[$name][$date] = 1;
                }
            } else {
                if($setDates){
                    $ts = strtotime($date);
                    if($ts >= $sDate && $ts<= $eDate){
                        $output[$name][$date]++;
                    }
                }else {
                    $output[$name][$date]++;
                }
            }
        }
    }
    echo '<pre>';
    print_r($output);
    echo '<pre>';
}

function getMax($csv){
    $output['success'] = false;
    $output['high'] = 0;

    $dataArr = buildArray($csv);

    foreach($dataArr as $k=>$v){
        $max = $v['maxScore'];

        if($output['high'] < $max){
            $output['high'] = $max;
            $output['success'] = true;
        }
    }
    echo json_encode($output);
}

function popStudents($csv){
    $output['students'] = [];
    $output['success'] = false;

    $dataArr = buildArray($csv);

    if(count($dataArr) > 1){
        foreach ($dataArr as $k=>$v){
            if($k !== 'Student Name') {
                $output['students'][] = $k;
            }
        }

        if(count($output['students'])){
            $output['success'] = true;
        }
    }
    echo json_encode($output);
}

?>