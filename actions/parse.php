<?php
//Set time zone to: America/Los_Angeles
date_default_timezone_set('America/Los_Angeles');

//Global Constants
$PPV = 2; //Prototype Point Value

if(isset($_POST['csvFile']) && $_POST['csvFile'] !== '') {
    $csv = $_POST['csvFile'];

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'prototype':
                include_once('../assets/parseCSS.php');
                if(isset($_POST['roster'])) {
                    $roster = $_POST['roster'];
                    showAllStudents($csv, $roster);
                } else {
                    echo '<h1>No class roster selected <a href=\'javascript:history.go(-1)\'>Return</a></h1>';
                }
                break;
            case 'rta':
                rta($csv);
                break;
            case 'report':
                include_once('../assets/reportCSS.php');
                if(isset($_POST['roster'])){
                    $roster = $_POST['roster'];
                    personalReport($csv, $roster);
                }else{
                    echo '<h1>No class roster selected <a href=\'javascript:history.go(-1)\'>Return</a></h1>';
                }
                break;
            case 'popStudents':
                if(isset($_POST['roster'])){
                    $roster = $_POST['roster'];
                    popStudents($csv, $roster);
                }else{
                    $output = [
                        'success' => false,
                        'error' => 'No class roster selected'
                    ];
                    echo json_encode($output);
                }
                break;
            case 'error':
                errorCheck($csv);
                break;
            case 'class list':
                getClassList($csv);
                break;
            default:
                echo 'No action chosen';
                break;
        }
    }else{
        echo '<h2>No action chosen <a href=\'javascript:history.go(-1)\'>Return</a></h2>';
    }
}else{
    echo '<h2>No CSV data found <a href=\'javascript:history.go(-1)\'>Return</a></h2>';
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
            $temp = [];
            for($i = 0; $i < count($output['index']); $i++){
                if($row[$i] != ''){
                    $temp[strtolower($output['index'][$i])] = $row[$i];
                }
            }
            $output['data'][] = $temp;
        }
        $count++;
    }

    return $output;
}

function getNameTs($title){
    $arr = explode(' - Due: ', $title);
    $output['name'] = $arr[0];

    $timeArr = explode('@', $arr[1]);
    $output['ts'] = strtotime($timeArr[0].$timeArr[1]);
    
    if(strpos($output['name'], 'FE | ') !== false || strpos($output['name'], 'BE | ') !== false){
        $nameArr = explode(' | ', $output['name']);
        $output['path'] = $nameArr[0];
        $output['name'] = $nameArr[1];
    }

    return $output;
}

function studentArray($arr, $roster){

    global $PPV;
    $output = [];

    foreach($arr['data'] as $v){

        if($v['tracking category'] == 'Prototype' && $v['class roster'] == $roster) {
            if (!isset($output[$v['student name']])) {
                $output[$v['student name']] = [
                    'class' => '',
                    'score' => 0,
                    'ontime' => 0,
                    'count' => 0,
                    'possible' => 0,
                    'path' => null,
                    'list' => []
                ];
            }

            if (strtolower($v['on time']) == 'yes') {
                $ontime = 1;
            } else {
                $ontime = 0;
            }
            $titleArr = getNameTs($v['tracking item']);
            $name = $v['student name'];
            $output[$name]['class'] = $v['class roster'];
            $output[$name]['score'] += $v['score'];
            $output[$name]['ontime'] += $ontime;
            $output[$name]['count']++;
            $output[$name]['possible'] += $PPV;
            $output[$name]['list'][] = [
                'pName' => $titleArr['name'],
                'pDue' => $titleArr['ts'],
                'pScore' => $v['score'],
                'pOntime' => $v['on time']
            ];
            if($output[$name]['path'] === null && isset($titleArr['path'])){
                $output[$name]['path'] = $titleArr['path'];
            }
        }
    }

    return $output;
}

function showAllStudents($csv, $roster){
    $rawData = buildArray($csv);
    $sData = studentArray($rawData, $roster);

    $html = '<table><tbody><tr><th>FE / BE</th><th>Student Name</th><th>% Turned In</th><th>% On Time</th><th>Personal Score</th><th>Overall Score</th><th>Avg Score</th></tr>';

    foreach($sData as $k=>$v){

        $l = getProtoList($rawData, $v, $roster);
        $turnedIn = round(($l['turnedIn']/$l['count']), 2)*100;
        $ontime = round(($v['ontime']/$l['ontime']), 2)*100;
        $pScore = round($v['score']/$v['possible'], 2)*100;
        $oScore = round($v['score']/($l['maxScore']), 2)*100;
        $avgScore = round($v['score']/$v['count'], 2);

        $html .= "<tr><td>$v[path]</td><th>$k</th><td>$turnedIn%</td><td>$ontime%</td><td>$pScore%</td><td>$oScore%</td><td>$avgScore</td></tr>";
    }

    $now = date('l, M j<\s\u\p>S</\s\u\p> Y \a\t H:i:s');

    $html .= "</tbody></table><p>Report Generated: $now</p><h3><a href='javascript:history.go(-1)'>Home</a></h3>";

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
        $title = explode(' -', $v['tracking item'])[0];
        $item = $v['student name'].' - '.$title;

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
                    $list[$name][] = $item.' - '.$date;
                }
            }else{
                $output[$name][$date]++;
                $list[$name][] = $item.' - '.$date;
            }
        }else{
            if($setDates){
                if($ts >= $sDate && $ts <= $eDate){
                    $output[$name][$date] = 1;
                    $list[$name][] = $item.' - '.$date;
                }
            }else{
                $output[$name][$date] = 1;
                $list[$name][] = $item.' - '.$date;
            }
        }
    }

    $output['Full List'] = $list;

    echo '<pre>';
    print_r($output);
    echo '</pre>';
}

function getProtoList($arr, $stuArr = false, $roster){
    global $PPV;
    $output = [];

    foreach($arr['data'] as $v){
        if($v['tracking category'] == 'Prototype' && $v['class roster'] == $roster) {
            $nameArr = getNameTs($v['tracking item']);
            if (!isset($output['protoList'][$nameArr['name']])) {
                if ($stuArr) {
                    if ((isset($nameArr['path']) && $nameArr['path'] == $stuArr['path']) || !isset($nameArr['path'])) {
                        $output['protoList'][$nameArr['name']] = $nameArr['ts'];
                    }
                } else {
                    $output['protoList'][$nameArr['name']] = $nameArr['ts'];
                }
            }
        }
    }

    $output['count'] = $output['ontime'] = count($output['protoList']);
    $output['maxScore'] = $output['count'] * $PPV;

    if($stuArr){
        $completed = 0;
        foreach($stuArr['list'] as $v){
            $output['protoList'][$v['pName']] = true;
            $completed++;
        }
        $output['missing'] = $output['count'] - $completed;
        $output['turnedIn'] = $completed;
    }

    return $output;
}

function protoTable($arr, &$html){

    $html .= '<table class="overview"><tbody><tr><th>Prototype</th><th>Score</th><th>On Time</th></tr>';

    foreach($arr as $v){
        $sClass = '';
        $oClass = '';

        $score = $v['pScore'];
        $ot = $v['pOntime'];
        $name = $v['pName'];

        if($score == 1){
            $sClass = 'yellow';
        }elseif($score == 0){
            $sClass = 'red';
        }

        if(strtolower($ot) == 'no'){
            $oClass = 'red';
        }

        $html .= "<tr  class='$sClass'><td>$name</td><td>$score</td><td class='$oClass'>$ot</td></tr>";
    }

    $html .= '</tbody></table>';
}

function statTable($arr, &$html){

    $overallTotalScore = $arr['totals']['maxScore'];
    $personalPossible = $arr['sData']['possible'];
    $personalEarned = $arr['sData']['score'];
    $personalPerc = round(($personalEarned/$personalPossible)*100, 2).'%';
    $overallPerc = round(($personalEarned/$overallTotalScore)*100, 2).'%';
    $ontimePerc = round(($arr['sData']['ontime']/$arr['totals']['ontime'])*100, 2).'%';
    $turnedIn = $arr['sData']['count'];
    $due = $arr['totals']['count'];

    $html .= "<table class='stats'><tbody><tr><th colspan='2'>$turnedIn of $due Prototypes Submitted</th></tr><tr><td>Personal Score of Reviewed Prototypes</td><th>$personalPerc</th></tr><tr><td>Overall Class Percentage: Based on $due Prototypes</td><th>$overallPerc</th></tr><tr><td>On Time Percentage (of reviewed)</td><th>$ontimePerc</th></tr></tbody></table>";
}

function missTable($arr, &$html){
    $html .= '<table id="miss-proto"><tr><th>Missing Prototypes</th><th>Due</th></tr>';
    $opt = false;
    $none = true;

    foreach($arr as $k=>$v){
        if($v !== true){
            $date = date('l, M j Y', $v);
            $none = false;
            $html .= "<tr><td>$k</td><td>$date</td></tr>";
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
}

function personalReport($csv, $roster){

    $output['success'] = false;
    if(isset($_POST['students'])){
        $sName = $_POST['students'];
    }else{
        echo '<h1>No name selected</h1><a style="font-size: 2em;" href="javascript:history.go(-1)">GO BACK</a>';
        return;
    }

    $rawData = buildArray($csv);
    $data['sData'] = studentArray($rawData, $roster)[$sName];
    $data['totals'] = getProtoList($rawData, $data['sData'], $roster);

    if(isset($data['sData']['path'])){
        $path = $data['sData']['path'];
        switch(strtolower($path)){
            case 'fe':
                $path = '( Frontend )';
                break;
            case 'be':
                $path = '( Backend )';
                break;
            default:
                $path = '( Undecided )';
        }
    }else{
        $path = '';
    }

    $html = "<h1 class='sName'>$sName $path</h1><div><a href='javascript:history.go(-1)'><img src='../assets/images/home-icon.png'></a></div>";

    protoTable($data['sData']['list'], $html);

    statTable($data, $html);

    missTable($data['totals']['protoList'], $html);

    $now = date('l, M j<\s\u\p>S</\s\u\p> Y \a\t H:i:s');

    $html .= "<p>Report Generated: <b>$now</b></p>";

    echo $html;
}

function getClassList($csv){
    $output['success'] = false;

    $dataArr = buildArray($csv);

    foreach($dataArr['data'] as $k=>$v){
        if(isset($v['class roster'])) {
            $class = $v['class roster'];
            if (!isset($temp[$class])) {
                $temp[$class] = $class;
            }
        }
    }

    if(isset($temp)){
        $output['class list'] = $temp;
        $output['success'] = true;
    }else {
        $output['error'] = 'No classes found';
    }

    echo json_encode($output);
}

function popStudents($csv, $roster){
    $output['students'] = [];
    $output['success'] = false;

    $dataArr = buildArray($csv);

    $studentData = studentArray($dataArr, $roster);

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

    $data = buildArray($csv);

    foreach($data['data'] as $k=>$v){

        $proto = explode(' -', $v['tracking item'])[0];
        $name = $v['student name'];

        if(!isset($raw[$name])){
            $raw[$name] = [];
        }
        $lineNum = $k + $lineOffset;
        if(!isset($raw[$name][$proto])){
            $raw[$name][$proto]['count'] = 1;
            $raw[$name][$proto]['info'][] = ['Line Number' => $lineNum, 'Reviewer' => $v['lfz reviewer'], 'Proto Name' => $proto];
        }else {
            $raw[$name][$proto]['count']++;
            $raw[$name][$proto]['info'][] = ['Line Number' => $lineNum, 'Reviewer' => $v['lfz reviewer'], 'Proto Name' => $proto];
            $output['errorCount']++;
            $output['errorList'][$name] = $raw[$name][$proto]['info'];
        }
    }
    $output['raw data'] = $raw;

    echo '<pre>';
    print_r($output);
    echo '</pre>';
}
?>