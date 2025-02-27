<?php
    // Проверка даты 
    function validateDate($date): bool {
        


        $format = 'j.n.Y H:i';
        $d = DateTime::createFromFormat($format, $date);
        if (!$d){
            print("ValidateDate: Invalid Date Format\n");
            return false;
        }
        $formatted_date = $d->format($format);
        print("ValidateDate formatted date: {$formatted_date}\n");
        return $formatted_date == $date;
    }

    // Проверка числа
    function validateNumber($number, $n, $m): bool{
        return (int)$number >= $n && (int)$number <= $m;
    }

    // Проверка имени на длину 
    function validateString($name, $n, $m): bool{
        return strlen($name) >= $n && strlen($name) <= $m;
    }

    // Провека почты
    function validateEmail($email): bool{
        $res = preg_match("/[a-zA-Z0-9][a-zA-Z0-9_]{3,29}@[a-zA-Z]{2,30}\.[a-z]{2,10}/", $email);
        print("Validate email result: {$res}\n");
        return $res;
    }

    // Проверка телефона
    function validatePhone($phone): bool{
        $res = preg_match("/\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}\$/", $phone);
        print("Parsing phone result: {$res}\n");
        return $res;
    }

    $input_lines = file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $out_file = fopen('output.txt', 'w');

    foreach ($input_lines as $line) {
        $line = trim($line);

        // Парсим из строки аргументы (шаблон обрабатывает все варианты)
        $elements_raw = array();
        preg_match_all("/<((?<=<)[^>]*)> ([A-Z]) ?(-?\d+)? ?(-?\d+)?/", $line, $elements_raw);
        
        // Парсим из каждой группы захвата шаблона строку
        $elements = array();
        foreach($elements_raw as $element){
            $elements[] = $element[0];
        }

        print_r($elements);
        $result = match($elements[2]){
            "S" => validateString($elements[1], (int)$elements[3], (int)$elements[4]),
            "N" => validateNumber((int)$elements[1], (int)$elements[3], (int)$elements[4]),
            "P" => validatePhone($elements[1]),
            "D" => validateDate($elements[1]),
            "E" => validateEmail($elements[1]),
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