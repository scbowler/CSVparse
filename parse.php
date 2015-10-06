<style>
    th {
        border: 1px solid black;
        text-align: center;
        padding: 1% 4px;
    }
    td {
       border: 1px solid black;
        text-align: center;
    }

    tr:nth-child(even) {
        background-color: lightgray;
    }
</style>


<?php

$output = [];
$csv = $_POST['csvFile'];
$action = $_POST['action'];

switch($action) {
    case 'prototype':
        prototype($csv);
        break;
    case 'rta':
        rta($csv);
        break;
    default:
        echo 'No action chosen';
        break;
}


function prototype($csv){
    global $output;

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

    $str = "<table><tr><th>Name</th><th>Score</th><th>Personal Possible Score</th><th>On Time</th><th>Possible On Time</th><th>Overall Possible</th><th>Personal Snapshot</th><th>Overall Snapshot</th></tr>";


    foreach ($output as $k=>$v) {
        if ($k !== 'Student Name') {

            $avg = round(($v['score'] + $v['ot']) / ($v['maxScore'] + $v['maxOt']), 2)*100;
            $avg2 = round(($v['score'] + $v['ot']) / (14 + 7), 2)*100;
            $str .= "<tr><td>$k</td><td>" . $v['score'] . "</td><td>" . $v['maxScore'] . "</td><td>" . $v['ot'] . "</td><td>" . $v['maxOt'] .
                "</td><td>14</td><td>$avg%</td><td>$avg2%</td></tr>";
        }
    }
        $str .= '</table>';

        echo $str;


//    echo '<pre>';
//    print_r($output);
//    echo '<pre>';
}

function rta($csv) {
    global $output;

    $sData = str_getcsv($csv, "\n", $enclosure = '"');

    foreach($sData as &$row) $row = str_getcsv($row, ",", $enclosure = '"');

    //$conv = explode(' ', $sData[1][0])[0];

    foreach($sData as $k=>$v){
        //print_r($v);
        //echo '<br>';

        $date = explode(' ', $v[0])[0];
        $name = $v[4];

        if (strpos($v[9], 'score')) {
            if (!isset($output[$name][$date])) {
                $output[$name][$date] = 1;
            } else {
                $output[$name][$date]++;
            }
        }
    }

    echo '<pre>';
    print_r($output);
    echo '<pre>';


}

?>