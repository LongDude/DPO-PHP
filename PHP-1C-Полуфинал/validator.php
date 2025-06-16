<?php
    function load_test_list($task){
        // Обнаруживает все файлы для тестирование задачи
        // in: $task - буква, обозначающая задачу
        // out: array - список файлов входных/выходных значений для задачи


        $test_dir = scandir("./tests/{$task}");
        $task_list = array();
        
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
                $task_list[$matches[1]] += [$matches[2] => "./tests/{$task}/{$filename}"];
            }
        }
        return $task_list;
    }
    
    // Запрашиваем номер задачи
    $task=null;
    while (!$task){
        $task= readline("Введите номер задачи: ");
        if (!in_array($task, ["A", "B", "C", "D"])){
            echo "Да нет такой задачи!";
            $task=null;
        }
    }

    // Загружаем все тесты для данной задачи
    $task_data = load_test_list($task); 
    $assert = true; // До первого сбоя

    // print_r($task_data);

    // Перебираем тесты
    foreach ($task_data as $task_number => $task_files){
        echo "Test [{$task_number}]...";
        
        // Небольшой костыль для запуска программ без явной привязки к валидатору
        copy($task_files['dat'], "input.txt");
        exec("php {$task}_solver.php");

        // Достаем фактические и теоретические результаты
        $result_current = file("output.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $result_real = file($task_files['ans'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Если массивы не идентичны, то программа выдала неверный результат 
        if ($result_current != $result_real){
            echo "Wrong!\n";
            echo "Expected:" . implode(' ', $result_real) . "\n";
            echo "Got     :" . implode(' ', $result_current) . "\n";
            
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

?>