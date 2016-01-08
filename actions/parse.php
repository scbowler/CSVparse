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
        //buildArray($csv);
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
    case 'error':
        errorCheck($csv);
        break;
    default:
        echo 'No action chosen';
        break;
}

function buildArray($csv){
    $output = [];

    $sData = str_getcsv($csv, "\n", $enclosure = '"');
    $count = 0;

    foreach($sData as &$row) {
        $row = str_getcsv($row, ",", $enclosure = '"');
        if($count == 0){
            $output['index'] = $row;
        }else{
            for($i = 0; $i < count($output['index']); $i++){
                $temp[$output['index'][$i]] = $row[$i];
            }
            $output['data'][] = $temp;
        }
        $count++;
    }
//    echo "<pre>";
//    print_r($output);
//    echo "</pre>";

    return $output;
}

function studentArray($arr){

    $output = [];
    $ontime = 0;

    foreach($arr['data'] as $v){

        if(!isset($output[$v['Student Name']])){
            $output[$v['Student Name']] = [
                'score' => 0,
                'ontime' => 0,
                'list' => []
            ];
        }

        if(strtolower($v['On time']) == 'yes'){
            $ontime = 1;
        }else{
            $ontime = 0;
        }

        $output[$v['Student Name']]['score'] += $v['Score'];
        $output[$v['Student Name']]['ontime'] += $ontime;
        $output[$v['Student Name']]['list'][] = [
            'pName' => $v['Tracking Item'],
            'pScore' => $v['Score'],
            'pOntime' => $v['On time']
        ];


//        if(isset($output[$v['Student Name']]['score'])){
//            $output[$v['Student Name']]['score'] += $v['Score'];
//        }else{
//            $output[$v['Student Name']]['score'] = $v['Score'];
//        }
//
//        if(strtolower($v['On time']) == 'yes'){
//            $ontime = 1;
//        }else{
//            $ontime = 0;
//        }
//
//        if(isset($output[$v['Student Name']]['ontime'])){
//            $output[$v['Student Name']]['ontime'] += $ontime;
//        }else{
//            $output[$v['Student Name']]['ontime'] = $ontime;
//        }
//
//        if(isset($output[$v['Student Name']]))

    }

    return $output;
}

function prototype($csv, $proto){
    $data = studentArray(buildArray($csv));

    echo "<pre>";
    print_r($data);
    echo "</pre>";


    // Timestamp
    // Student Name
    // LFZ Reviewer
    // Date Reviewed
    // Tracking Category
    // Tracking Item
    // Score
    // On time

//    foreach($data['data'] as $v){
//        $html = '<table><tbody><tr><th>Student Name</th><th></th></tr></tbody></table>';
//    }

//    $str = "<table><tr><th>Name</th><th>Score</th><th>Personal Possible Score</th><th>On Time</th><th>Possible On Time</th><th>Overall Possible</th><th>Personal Snapshot</th><th>Overall Snapshot</th></tr>";
//
//    foreach ($output as $k=>$v) {
//        if ($k !== 'Student Name') {
//
//            $avg = round(($v['score'] + $v['ot']) / ($v['maxScore'] + $v['maxOt']), 2)*100;
//            $avg2 = round(($v['score'] + $v['ot']) / ($proto + ($proto/2)), 2)*100;
//            $str .= "<tr><td>$k</td><td>" . $v['score'] . "</td><td>" . $v['maxScore'] . "</td><td>" . $v['ot'] . "</td><td>" . $v['maxOt'] .
//                "</td><td>$proto</td><td>$avg%</td><td>$avg2%</td></tr>";
//        }
//    }
//        $str .= '</table><h3><a href="../index.php">Home</a></h3>';
//
//        echo $str;
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

function getProtoList($arr){
    $output = [];

    foreach($arr as $v){
        if($v[9] != 'Tracking Item') {
            $proto = explode(' (', $v[9])[0];
            if (!isset($output[$proto])) {
                $output[$proto] = false;
            }
        }
    }
    return $output;
}

function missTable($arr){
    $html = '<table id="miss-proto"><tr><th>Missing Prototypes</th></tr>';
    $opt = false;
    $none = true;

    foreach($arr as $k=>$v){
        if(!$v){
            $none = false;
            $html .= "<tr><td>$k</td></tr>";
            if(strpos($k, '*')){
                $opt = true;
            }
        }
    }

    end($arr);
    $k = key($arr);

    if($none){
        $html .= '<tr class="sp-note"><td>No missing prototypes</td></tr>';
    }
    if(!$arr[$k]){
        $html .= "<tr class='sp-note'><td>$k may not yet be reviewed</td></tr>";
    }
    if($opt){
        $html .= '<tr class="sp-note"><td>Prototypes with * are optional</td></tr>';
    }
    $html .= '</table>';

    return $html;
}

function personalReport($csv){

    $output['success'] = false;

    $sName = $_POST['students'];

    $sData = str_getcsv($csv, "\n", $enclosure = '"');

    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');

    $output['protoList'] = getProtoList($sData);

//    echo "<pre>";
//    print_r($output);
//    echo "</pre>";
//    return;

    $output['totalScore'] = 0;
    $output['totalOnTime'] = 0;

    foreach($sData as $k=>$v){
        if($v[2] == $sName) {
            $proto = explode(' (', $v[9])[0];
            if (isset($output['protoList'][$proto])){
                $output['protoList'][$proto] = true;
            }
            if(strpos($v[9], 'score') || strpos($v[9], 'Score')) {
                $output['name'] = $v[2];
                $output['proto'][] = $proto;
                $output['score'][] = $v[10];
                $output['totalScore'] += $v[10];
            }else{
                $output['complete'][] = $v[9];
                if($v[10] == 1) {
                    $output['ontime'][] = 'Yes';
                    $output['totalOnTime']++;
                }else if($v[10] == 0){
                    $output['ontime'][] = 'No';
                }else{
                    $output['ontime'][] = "ERROR $v[10] $v[0]";
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
        $html = '<h1 class="sName">' . $output['name'] . '</h1><div><a href="../index.php"><img src="../assets/images/home-icon.png"></a></div><table class="overview"><tr><th>Prototype</th><th>Score</th><th>On-Time</th></tr>';

        for ($i = 0; $i < $len; $i++) {
            $score = $output['score'][$i];
            $class = '';
            $otClass = '';
            $missingData = '';

            if(isset($output['ontime'][$i])){
                $ot = $output['ontime'][$i];
                if($ot == 'No' || strpos($ot, 'ERROR')){
                    $otClass = 'red';
                }
            }else{
                $otClass = 'gray';
                $ot = 'Yes';
                $missingData = '*';
                $output['totalOnTime']++;
            }
            if($score == 1){
                $class = 'yellow';
            }else if($score == 0){
                $class = 'red';
            }

            $html .= '<tr class="'.$class.'"><td>' . $output['proto'][$i] . '</td><td>' . $score . '</td><td class="'.$otClass.'">' . $ot . '</td></tr>';
        }
        $overallTotal = $_POST['maxProto'];
        $personalPerc = round((($output['totalScore']/($len*2))*100),2).'%';
        $overallPerc = round((($output['totalScore']/$overallTotal)*100),2).'%';
        $ontime = round(($output['totalOnTime']/$len)*100,2).'%'.$missingData;

        $html .= '<table class="stats"><tr><th colspan="2">'.$len.' of '.round($overallTotal/2,0).' Prototypes Submitted</th></tr><tr><td>Personal Score of Reviewed Prototypes</td><th>'.$personalPerc.'</th></tr>
        <tr><td>Overall class percentage based on '.round($overallTotal/2,0).' prototypes</td><th>'.$overallPerc.'</th></tr>
        <tr><td>On-time percentage (of reviewed)</td><th>'.$ontime.'</th></tr></table>'.missTable($output['protoList']).'<p>If * is present on "On-Time" percentage, data is missing from tracker</p>'.$offset;
    }
    //$html .= ;

    echo $html;

//    echo '<pre>';
//    print_r($output);
//    echo '</pre>';
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

function errorCheck($csv){
    echo "<h1>Error Check Results</h1>";
    $output['errorCount'] = 0;
    $stuChk = [];

    $sData = str_getcsv($csv, "\n", $enclosure = '"');

    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');

    foreach($sData as $k=>$v){
        if($k != 0) {
            $proto = explode(' (', $v[9])[0];
            if(isset($stuChk[$v[2]][$proto])){
                $stuChk[$v[2]][$proto]['count']++;
            }else{
                $stuChk[$v[2]][$proto]['count'] = 1;
            }
            $stuChk[$v[2]][$proto]['last-line'] = $k;
            if (strpos($v[9], 'complete') || strpos($v[9], 'Complete')) {
                if ($v[11] != 1) {
                    $output['row'][] = $k;
                    $output['errorType'][] = 'completed';
                    $output['maxValue'][] = $v[11];
                    $output['test'][] = explode(' (', $v[9])[0];
                    $output['errorCount']++;
                }
            } else if (strpos($v[9], 'score') || strpos($v[9], 'Score')) {
                if ($v[11] != 2) {
                    $output['row'][] = $k;
                    $output['errorType'][] = 'score';
                    $output['maxValue'][] = $v[11];
                    $output['test'][] = explode(' (', $v[9])[0];
                    $output['errorCount']++;
                }
            } else {
                $output['row'][] = $k;
                $output['errorType'][] = 'Invalid Item';
                $output['item'][] = $v[9];
                $output['test'][] = explode(' (', $v[9])[0];
                $output['errorCount']++;
            }
        }
    }

    foreach($stuChk as $k=>$v){
        $output[$k]['mistakes'] = 0;
        foreach($stuChk[$k] as $k2=>$v2){
            if($v2['count'] != 2){
                $output[$k]['mistakes']++;
                $output[$k][$k2] = $v2;
                $output[$k][$k2]['last-line'] = $v2['last-line'];
                $output['errorCount']++;
            }
        }
    }

//    echo '<pre>';
//    print_r($stuChk);
//    echo '</pre>';
    echo '<pre>';
    print_r($output);
    echo '</pre>';
}

?>