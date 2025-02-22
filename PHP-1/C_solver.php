<?php
    function validateDate($date, $format = 'Y.m.d') {
        $d = DateTime::createFromFormat('d.m.Y H:i', $date);
        return $d && $d->format($format) == $date;
    }

    function validateNumber($number, $n, $m){
        return $number >= $n && $number <= $m;
    }
    $input_lines = file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $out_file = fopen('output.txt', 'w');

    foreach ($input_lines as $line) {
        $line = trim($line);
        $elements = array();
        preg_match_all("/<((?<=<)[^>]*)> ([A-Z]) ?(\d+)? ?(\d+)?/", $line, $elements);
        
        $result = match($elements[2][0]){
            "S" => preg_match("/*{" . $elements[3][0] . ", " . $elements[4][0] . "}/", $elements[1][0]),
            "N" => validateNumber((int)filter_var($elements[1][0], FILTER_VALIDATE_INT), (int)$elements[3][0], (int)$elements[4][0]),
            "P" => preg_match("/\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}/", $elements[1][0]),
            "D" => validateDate($elements[1][0]),
            "E" => preg_match("/[a-zA-Z0-9][a-zA-Z0-9_]{3,29}@[a-zA-Z]{2,30}\.[a-z]{2,10}/", $elements[1][0])
        };
        
        if ($result){
            fwrite($out_file, "OK\n");
        }
        else {
            fwrite($out_file, "FAIL\n");
        }
    }

    fclose($out_file);
?>