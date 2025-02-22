<?php
    $keywords = readline('Введите строку=');

    $result = array();
    print_r($result);
    echo "\n";

    $result = preg_split('/(?<=\')(\d+)(?=\')/', $keywords, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    print_r($result);
    echo "\n";
    

    $new_result = array();

    foreach ($result as $val ){
        if (is_numeric($val)){
            $val *= 2;
        }
        $new_result[] = $val;
    }
    $answer = implode('', $new_result);
    print_r($answer);
?>