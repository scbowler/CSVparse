<?php

if(isset($_POST['csvFile'])) {
    $csv = $_POST['csvFile'];
}
if(isset($_POST['action'])) {
    $action = $_POST['action'];
}
if(isset($_POST['maxProto'])) {
    $proto = $_POST['maxProto'];
}

switch($action) {
    case 'prototype':
        include_once('../assets/parseCSS.php');
        prototype($csv, $proto);
        break;
    case 'rta':
        rta($csv);
        break;
    case 'report':
        include_once('../assets/reportCSS.php');
        personalReport($csv);
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
            if (strpos($aRow[9], 'score') || strpos($aRow[9], 'Score')) {
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
                    $output[$aRow[2]]['maxOt']++;
                } else {
                    $output[$aRow[2]]['ot'] = (int)$aRow[10];
                    $output[$aRow[2]]['maxOt'] = 1;
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

        if (strpos($v[9], 'score') || strpos($v[9], 'Score')) {
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

function personalReport($csv){

    $output['success'] = false;

    $sName = $_POST['students'];

    $sData = str_getcsv($csv, "\n", $enclosure = '"');

    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');

    $output['totalScore'] = 0;
    $output['totalOnTime'] = 0;

    foreach($sData as $v){
        if($v[2] == $sName) {
            if(strpos($v[9], 'score') || strpos($v[9], 'Score')) {
                $output['name'] = $v[2];
                $output['proto'][] = $v[9];
                $output['score'][] = $v[10];
                $output['totalScore'] += $v[10];
            }else{
                $output['complete'][] = $v[9];
                if($v[10] == 1) {
                    $output['ontime'][] = 'Yes';
                    $output['totalOnTime']++;
                }else if($v[10] == 0){
                    $output['ontime'][] = 'No';
                }
            }
        }
    }
    $len = count($output['proto']);
    $lenOt = count($output['ontime']);

    if($len > 0) {
        $output['success'] = true;
        $offset = '<p>Score and Complete match</p>';
        if($len > $lenOt){
            $missing = $len - $lenOt;
            $ent = 'entry';
            if($missing > 1){
                $ent = 'entries';
            }
            $offset = "<p>There is $missing extra ".'"score" '.$ent."</p>";
        }else if($len < $lenOt){
            $missing = $lenOt - $len;
            $ent = 'entry';
            if($missing > 1){
                $ent = 'entries';
            }
            $offset = "<p>There is $missing extra ".'"complete" '.$ent."</p>";
        }
        $html = '<h1 class="sName">' . $output['name'] . '</h1><h3><a href="../index.php">Home</a></h3><table class="overview"><tr><th>Prototype</th><th>Score</th><th>On-Time</th></tr>';

        for ($i = 0; $i < $len; $i++) {
            $score = $output['score'][$i];
            $class = '';
            $ot = $output['ontime'][$i];
            $otClass = '';
            $missingData = '';

            if($score == 1){
                $class = 'yellow';
            }else if($score == 0){
                $class = 'red';
            }
            if($ot == 'No'){
                $otClass = 'red';
            }else if($ot == ''){
                $otClass = 'gray';
                $ot = 'Yes';
                $missingData = '*';
                $output['totalOnTime']++;
            }

            $html .= '<tr class="'.$class.'"><td>' . $output['proto'][$i] . '</td><td>' . $score . '</td><td class="'.$otClass.'">' . $ot . '</td></tr>';
        }
        $overallTotal = $_POST['maxProto'];
        $personalPerc = round((($output['totalScore']/($len*2))*100),2).'%';
        $overallPerc = round((($output['totalScore']/$overallTotal)*100),2).'%';
        $ontime = round(($output['totalOnTime']/$len)*100,2).'%'.$missingData;

        $html .= '<table class="stats"><tr><th colspan="2">'.$len.' of '.round($overallTotal/2,0).' Prototypes Submitted</th></tr><tr><td>Personal Score of Reviewed Prototypes</td><th>'.$personalPerc.'</th></tr>
        <tr><td>Overall class percentage based on '.round($overallTotal/2,0).' prototypes</td><th>'.$overallPerc.'</th></tr>
        <tr><td>On-time percentage (of reviewed)</td><th>'.$ontime.'</th></tr></table><p>If * is present on "On-Time" percentage, data is missing from tracker</p>'.$offset;
    }
    echo $html;

    echo '<pre>';
    print_r($output);
    echo '</pre>';
}

function getMax($csv){
    $output['success'] = false;
    $output['high'] = 0;

    $dataArr = buildArray($csv);

    foreach($dataArr as $k=>$v){
        if(isset($v['maxScore'])) {
            $max = $v['maxScore'];

            if ($output['high'] < $max) {
                $output['high'] = $max;
                $output['success'] = true;
            }
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