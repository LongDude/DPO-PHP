<?php

function solve(array $in_data): array{
    $SEC_IN_HOUR = 60*60;
    $fp = 0; // указатель на текущую строку
    $result = [];

    $n = $in_data[$fp++];
    for ($i = 0; $i < $n; $i++){
        [$date_depart, $timezone_depart, $date_arrival, $timezone_arrival] = explode(' ', $in_data[$fp++]);

        $date_depart = new DateTime(preg_replace('/_/', ' ',$date_depart));
        $date_arrival = new DateTime(preg_replace('/_/', ' ',$date_arrival));

        $length = ($date_arrival->getTimestamp() + $SEC_IN_HOUR*$timezone_depart) - ($date_depart->getTimestamp()+$SEC_IN_HOUR*$timezone_arrival);
        $result[] = $length;
    }

    return $result;
}

// Тестируем результат
$test_dir = scandir("./tests/A");
$task_list = array();

// Загружаем все тесты для данной задачи
// Перебираем файлы в директории
foreach($test_dir as $filename){
    // Отделяем файлы с условиями .dat от файлов с ответами .ans
    $matches = [];
    $m = preg_match("/(\d+)\.(dat|ans)/", $filename, $matches);
    if ($m){
        // Для каждого текста в массиве сохраняем соотвествующий файл
        if (!array_key_exists($matches[1], $task_list)){
            $task_list += [$matches[1] => array()]; // Инициализация ключа
        }
        $task_list[$matches[1]] += [$matches[2] => "./tests/A/{$filename}"];
    }
}


// print_r($task_data);
// Перебираем тесты
$assert = true; // До первого сбоя
foreach ($task_list as $task_number => $task_file){
    echo "Test [{$task_number}]...";

    $data = file($task_file['dat']);
    $result = solve($data);

    // Достаем фактические и теоретические результаты
    $result_expected = file($task_file['ans'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Если массивы не идентичны, то программа выдала неверный результат 
    if ($result != $result_expected){
        echo "Wrong!\n";
        foreach ($result as $i => $res_str) {
            if ($res_str != $result_expected[$i]){
                echo "Line " . $i+1 . "\n";
                echo "Expected:" . $result_expected . "\n";
                echo "Got     :" .  $result . "\n";
            }
        }
        
        // Полностью прерываем тестирование
        $assert = false; 
        break;
    }
    else {
        echo "OK\n";
    }
}

if ($assert){
    echo "Task completed!\n";
}
else {
    echo "Wrong answer!\n";
}