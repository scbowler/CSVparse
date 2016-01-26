<?php
//Global Constants
$PPV = 2; //Prototype Point Value


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
        showAllStudents($csv, $proto);
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

    global $PPV;
    $output = [];

    foreach($arr['data'] as $v){

        if($v['Tracking Category'] == 'Prototype') {
            if (!isset($output[$v['Student Name']])) {
                $output[$v['Student Name']] = [
                    'score' => 0,
                    'ontime' => 0,
                    'count' => 0,
                    'possible' => 0,
                    'list' => []
                ];
            }

            if (strtolower($v['On time']) == 'yes') {
                $ontime = 1;
            } else {
                $ontime = 0;
            }

            $output[$v['Student Name']]['score'] += $v['Score'];
            $output[$v['Student Name']]['ontime'] += $ontime;
            $output[$v['Student Name']]['count']++;
            $output[$v['Student Name']]['possible'] += $PPV;
            $output[$v['Student Name']]['list'][] = [
                'pName' => $v['Tracking Item'],
                'pScore' => $v['Score'],
                'pOntime' => $v['On time']
            ];
        }
    }

    return $output;
}

function showAllStudents($csv, $proto){
    global $PPV;
    $data = studentArray(buildArray($csv));

    $html = '<table><tbody><tr><th>Student Name</th><th>% Turned In</th><th>% On Time</th><th>Personal Score</th><th>Overall Score</th><th>Avg Score</th></tr>';

    foreach($data as $k=>$v){
        $turnedIn = round($v['count']/$proto, 2)*100;
        $ontime = round($v['ontime']/$proto, 2)*100;
        $pScore = round($v['score']/$v['possible'], 2)*100;
        $oScore = round($v['score']/($proto * $PPV), 2)*100;
        $avgScore = round($v['score']/$v['count'], 2);

        $html .= "<tr><th>$k</th><td>$turnedIn%</td><td>$ontime%</td><td>$pScore%</td><td>$oScore%</td><td>$avgScore</td></tr>";
    }

    $html .= '</tbody></table><h3><a href="javascript:history.go(-1)">Home</a></h3>';

     echo $html;
}

function rta($csv) {
    $output = [];
    $list = [];

    $setDates = false;

    $sDate = strtotime($_POST['start-date']);
    $eDate = strtotime($_POST['end-date']);

    $data = buildArray($csv)['data'];

    if($sDate > 0){
        if($eDate == 0){
            $eDate = time();
        }
        $setDates = true;
    }

    foreach($data as $v){
        $date = $v['Date Reviewed'];
        $ts = strtotime($date);
        $name = $v['LFZ Reviewer'];
        $title = explode(' -', $v['Tracking Item'])[0];
        $item = $v['Student Name'].' - '.$title;

        if(isset($output[$name]['total'])){
            if($setDates) {
                if ($ts >= $sDate && $ts <= $eDate) {
                    $output[$name]['total']++;

                }
            }else{
                $output[$name]['total']++;
            }
        }else{
            if($setDates) {
                if ($ts >= $sDate && $ts <= $eDate) {
                    $output[$name]['total'] = 1;
                }
            }else{
                $output[$name]['total'] = 1;
            }
        }

        if(isset($output[$name][$date])){
            if($setDates){
                if($ts >= $sDate && $ts <= $eDate){
                    $output[$name][$date]++;
                    $list[$name][] = $item;
                }
            }else{
                $output[$name][$date]++;
                $list[$name][] = $item;
            }
        }else{
            if($setDates){
                if($ts >= $sDate && $ts <= $eDate){
                    $output[$name][$date] = 1;
                    $list[$name][] = $item;
                }
            }else{
                $output[$name][$date] = 1;
                $list[$name][] = $item;
            }
        }
    }

    $output['Full List'] = $list;

    echo '<pre>';
    print_r($output);
    echo '</pre>';
}

function getProtoList($arr){
    global $PPV;
    $output = [];

    foreach($arr['data'] as $v){
        $proto = explode(' - ', $v['Tracking Item'])[0];
        if(!isset($output['protoList'][$proto])){
            $output['protoList'][$proto] = false;
        }
    }

    $output['count'] = $output['ontime'] = count($output['protoList']);
    $output['maxScore'] = $output['count'] * $PPV;

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
    if(isset($_POST['students'])){
        $sName = $_POST['students'];
    }else{
        echo '<h1>No name selected</h1><a style="font-size: 2em;" href="javascript:history.go(-1)">GO BACK</a>';
        return;
    }

    $rawData = buildArray($csv);
    $data['sData'] = studentArray($rawData)[$sName];
    $data['protoList'] = getProtoList($rawData);


    echo '<pre>';
    print_r($data);
    echo '</pre>';

//    $sName = $_POST['students'];
//
//    $sData = str_getcsv($csv, "\n", $enclosure = '"');
//
//    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');
//
//    $output['protoList'] = getProtoList($sData);
//
//    $output['totalScore'] = 0;
//    $output['totalOnTime'] = 0;
//
//    foreach($sData as $k=>$v){
//        if($v[2] == $sName) {
//            $proto = explode(' (', $v[9])[0];
//            if (isset($output['protoList'][$proto])){
//                $output['protoList'][$proto] = true;
//            }
//            if(strpos($v[9], 'score') || strpos($v[9], 'Score')) {
//                $output['name'] = $v[2];
//                $output['proto'][] = $proto;
//                $output['score'][] = $v[10];
//                $output['totalScore'] += $v[10];
//            }else{
//                $output['complete'][] = $v[9];
//                if($v[10] == 1) {
//                    $output['ontime'][] = 'Yes';
//                    $output['totalOnTime']++;
//                }else if($v[10] == 0){
//                    $output['ontime'][] = 'No';
//                }else{
//                    $output['ontime'][] = "ERROR $v[10] $v[0]";
//                }
//            }
//        }
//    }
//    $len = count($output['proto']);
//    $lenOt = count($output['ontime']);
//
//    if($len > 0) {
//        $output['success'] = true;
//        $offset = '<p>Score and Complete match</p>';
//        if($len > $lenOt){
//            $missing = $len - $lenOt;
//            $ent = 'entry';
//            if($missing > 1){
//                $ent = 'entries';
//            }
//            $offset = "<p>There is $missing extra ".'"score" '.$ent."</p>";
//        }else if($len < $lenOt){
//            $missing = $lenOt - $len;
//            $ent = 'entry';
//            if($missing > 1){
//                $ent = 'entries';
//            }
//            $offset = "<p>There is $missing extra ".'"complete" '.$ent."</p>";
//        }
//        $html = '<h1 class="sName">' . $output['name'] . '</h1><div><a href="../index.php"><img src="../assets/images/home-icon.png"></a></div><table class="overview"><tr><th>Prototype</th><th>Score</th><th>On-Time</th></tr>';
//
//        for ($i = 0; $i < $len; $i++) {
//            $score = $output['score'][$i];
//            $class = '';
//            $otClass = '';
//            $missingData = '';
//
//            if(isset($output['ontime'][$i])){
//                $ot = $output['ontime'][$i];
//                if($ot == 'No' || strpos($ot, 'ERROR')){
//                    $otClass = 'red';
//                }
//            }else{
//                $otClass = 'gray';
//                $ot = 'Yes';
//                $missingData = '*';
//                $output['totalOnTime']++;
//            }
//            if($score == 1){
//                $class = 'yellow';
//            }else if($score == 0){
//                $class = 'red';
//            }
//
//            $html .= '<tr class="'.$class.'"><td>' . $output['proto'][$i] . '</td><td>' . $score . '</td><td class="'.$otClass.'">' . $ot . '</td></tr>';
//        }
//        $overallTotal = $_POST['maxProto'];
//        $personalPerc = round((($output['totalScore']/($len*2))*100),2).'%';
//        $overallPerc = round((($output['totalScore']/$overallTotal)*100),2).'%';
//        $ontime = round(($output['totalOnTime']/$len)*100,2).'%'.$missingData;
//
//        $html .= '<table class="stats"><tr><th colspan="2">'.$len.' of '.round($overallTotal/2,0).' Prototypes Submitted</th></tr><tr><td>Personal Score of Reviewed Prototypes</td><th>'.$personalPerc.'</th></tr>
//        <tr><td>Overall class percentage based on '.round($overallTotal/2,0).' prototypes</td><th>'.$overallPerc.'</th></tr>
//        <tr><td>On-time percentage (of reviewed)</td><th>'.$ontime.'</th></tr></table>'.missTable($output['protoList']).'<p>If * is present on "On-Time" percentage, data is missing from tracker</p>'.$offset;
//    }
    //$html .= ;

//    echo $html;

//    echo '<pre>';
//    print_r($output);
//    echo '</pre>';
}

function getMax($csv){
    $output['success'] = false;

    $dataArr = buildArray($csv);

    $protoList = getProtoList($dataArr);

    if($protoList['count']){
        $output['count'] = $protoList['count'];
        $output['success'] = true;
    }

    echo json_encode($output);
}

function popStudents($csv){
    $output['students'] = [];
    $output['success'] = false;

    $dataArr = buildArray($csv);

    $studentData = studentArray($dataArr);

    if(count($studentData)){
        foreach($studentData as $k=>$v){
            $output['students'][] = $k;
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
    $output['errorList'] = [];
    $raw = [];
    $lineOffset = 2;
    $stuChk = [];

    $data = buildArray($csv);

//    echo '<pre>';
//    print_r($data);
//    echo '</pre>';

    foreach($data['data'] as $k=>$v){

        $proto = explode(' -', $v['Tracking Item'])[0];
        $name = $v['Student Name'];

        if(!isset($raw[$name])){
            $raw[$name] = [];
        }
        $lineNum = $k + $lineOffset;
        if(!isset($raw[$name][$proto])){
            $raw[$name][$proto]['count'] = 1;
            $raw[$name][$proto]['info'][] = ['Line Number' => $lineNum, 'Reviewer' => $v['LFZ Reviewer'], 'Proto Name' => $proto];
        }else {
            $raw[$name][$proto]['count']++;
            $raw[$name][$proto]['info'][] = ['Line Number' => $lineNum, 'Reviewer' => $v['LFZ Reviewer'], 'Proto Name' => $proto];
            $output['errorCount']++;
            $output['errorList'][$name] = $raw[$name][$proto]['info'];
        }
    }
    $output['Raw Data'] = $raw;

    echo '<pre>';
    print_r($output);
    echo '</pre>';

//    $sData = str_getcsv($csv, "\n", $enclosure = '"');
//
//    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');
//
//    foreach($sData as $k=>$v){
//        if($k != 0) {
//            $proto = explode(' (', $v[9])[0];
//            if(isset($stuChk[$v[2]][$proto])){
//                $stuChk[$v[2]][$proto]['count']++;
//            }else{
//                $stuChk[$v[2]][$proto]['count'] = 1;
//            }
//            $stuChk[$v[2]][$proto]['last-line'] = $k;
//            if (strpos($v[9], 'complete') || strpos($v[9], 'Complete')) {
//                if ($v[11] != 1) {
//                    $output['row'][] = $k;
//                    $output['errorType'][] = 'completed';
//                    $output['maxValue'][] = $v[11];
//                    $output['test'][] = explode(' (', $v[9])[0];
//                    $output['errorCount']++;
//                }
//            } else if (strpos($v[9], 'score') || strpos($v[9], 'Score')) {
//                if ($v[11] != 2) {
//                    $output['row'][] = $k;
//                    $output['errorType'][] = 'score';
//                    $output['maxValue'][] = $v[11];
//                    $output['test'][] = explode(' (', $v[9])[0];
//                    $output['errorCount']++;
//                }
//            } else {
//                $output['row'][] = $k;
//                $output['errorType'][] = 'Invalid Item';
//                $output['item'][] = $v[9];
//                $output['test'][] = explode(' (', $v[9])[0];
//                $output['errorCount']++;
//            }
//        }
//    }
//
//    foreach($stuChk as $k=>$v){
//        $output[$k]['mistakes'] = 0;
//        foreach($stuChk[$k] as $k2=>$v2){
//            if($v2['count'] != 2){
//                $output[$k]['mistakes']++;
//                $output[$k][$k2] = $v2;
//                $output[$k][$k2]['last-line'] = $v2['last-line'];
//                $output['errorCount']++;
//            }
//        }
//    }
//
////    echo '<pre>';
////    print_r($stuChk);
////    echo '</pre>';
//    echo '<pre>';
//    print_r($output);
//    echo '</pre>';
}

?>