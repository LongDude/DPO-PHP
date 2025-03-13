<?php
    // Скрипт чтения тестовых данных с xlsx файла
    // На выходе используем переменную $test_data
    include 'read_xlsx.php';
    
    // В отдельных файлах раскиданы функции для решения задач
    include 'A_solver.php';
    include 'B_solver.php';
    include 'C_solver.php';
    include 'D_solver.php';

    // $task = null;
    $task = 'D'; // все еще не могу считывать ввод в дебаггере
    while(!$task){
        $task = readline("Введите номер задачи: ");
        if (!in_array($task, ['A', 'B', 'C', 'D'])){
            echo "Да нет такой задачи!";
            $task = null;
        }
    }

    // Метод научного тыка
    // var_dump(isset($test_data['Задача А'])); // Rus
    // var_dump(isset($test_data['Задача В'])); // Rus
    // var_dump(isset($test_data['Задача C'])); // Eng
    // var_dump(isset($test_data['Задача D'])); // Eng
    // Кто-то подложил в имена задач A,B,C,D кириллические буквы...
    $task = match($task){
        "A" => "Задача А",
        "B" => "Задача В",
        "C" => "Задача C",
        "D" => "Задача D",
        default => die("неожиданный номер задачи")
    };

    foreach($test_data[$task] as $test_num => $test){
        echo 'TEST ' . "$test_num" . ': ';
        $res = trim(match($task){
            "Задача А" => solve_a($test['inp']),
            "Задача В" => solve_b($test['inp']),
            "Задача C" => solve_c($test['inp']),
            "Задача D" => solve_d($test['inp']),
        });
        if ($task!='Задача C' && $res != $test['out']){
            echo "ERROR!\n";
            echo "Expected:\n";
            print_r($test['out']);
            echo "\nGot:\n";
            print_r($res);
            die("Terminating"); 
        } 
        elseif ($task == 'Задача C') {
            // Для задачи C проверка должна быть нежесткая
            // т.к. по условию ответы совпадают с погрешностью :/
            $res_lines = explode("\n", $res);
            $tst_lines = explode("\n", trim($test['out']));

            foreach($tst_lines as $tst_id => $tst_dat){
                if (!isset($res_lines[$tst_id])){
                    echo "Error: expected\n";
                    print_r($tst_dat);
                    echo "\nGot: nothing\n";
                    die("Terminating");
                }
                // достаем параметры из ожидаемого и полученного результатов
                $tst_dat = explode(' ', $tst_dat);
                $res_dat = explode(' ', $res_lines[$tst_id]);
                if ($tst_dat[0] != $res_dat[0] || abs((float)$tst_dat - (float)$res_dat) > 0.001){
                    echo "Error:\n";
                    echo "Expected:\n";
                    print_r($tst_dat);
                    echo "Got:\n";
                    print_r($res_dat);
                    die("Terminating");
                }
            }
            echo "OK!\n";
        }
        else {
            echo "OK!\n";
        };
    }
?>