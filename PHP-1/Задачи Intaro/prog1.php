<?php
    $keywords = readline('Введите строку=');

    $result = array();
    print_r($result);
    echo "\n";
    // Разбивает строку по шаблону на просто строки и числа в кавычках
    $result = preg_split('/(?<=\')(\d+)(?=\')/', $keywords, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    print_r($result);
    echo "\n";
    

    $new_result = array();
    foreach ($result as $val ){
        if (is_numeric($val)){ // перебираем числа, которые надо увеличить
            $val *= 2;
        }
        $new_result[] = $val;
    }
    // Добавляем в ответ
    $answer = implode('', $new_result);
    print_r($answer);
?>