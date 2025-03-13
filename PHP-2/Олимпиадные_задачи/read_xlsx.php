<?php
    // include __DIR__ . "./A_solver.php";
    // include __DIR__ . "./B_solver.php";
    // include __DIR__ . "./C_solver.php";
    // include __DIR__ . "./D_solver.php";

    // Парсинг файла с тестами
    $f = new ZipArchive();
    $filepath = __DIR__ . "/test.xlsx";
    $err = $f->open($filepath, ZipArchive::RDONLY);

    if (!file_exists($filepath)) {
        die("File not exists\n");
    }

    if ($err !== true) {
        echo 'failed, code:';
        switch ($err){
            case ZipArchive::ER_EXISTS:
                echo "Already exists\n";
                break;
            case ZipArchive::ER_NOENT: 
                echo "Not exists\n";
                break;
        }
    }

    $test_values=array();
    if ($fp=$f->getStream('xl/sharedStrings.xml')){
        $data = '';
        while (!feof($fp)){
            $data .= fread($fp, 1024);
        }
        fclose($fp);

        $xml=simplexml_load_string($data);
        if (isset( $xml->si) && count($xml->si)){
            foreach ($xml->si as $data){
                $data = (array)$data;
                $test_values[]=$data['t'];
            }
        }
    }

    $xls_values=array();

    if ($fp=$f->getStream('xl/worksheets/sheet1.xml')){
        $data = '';
        while (!feof($fp)){
            $data .= fread($fp,1024);
        }
        fclose($fp);

        $xml = simplexml_load_string($data);
        
        if (isset( $xml->sheetData)){
            $sheetData = (array)($xml->sheetData);
            if (isset($sheetData['row']) && count($sheetData['row']) > 0){
                foreach($sheetData['row'] as $row){
                    $row = (array)$row;

                    // Для одноколоночной таблицы
                    if (!is_array($row['c'])){
                        $row['c']=array($row['c']);
                    }

                    foreach($row['c'] as $col){
                        $col=(array)$col;

                        // Столбец и колонка
                        preg_match('/([A-Z]+)(\d+)/', $col['@attributes']['r'], $matches);

                        if(isset($col['@attributes']['t'])
                        && $col['@attributes']['t'] == 's'
                        && isset($test_values[$col['v']])){
                            $xls_values[$matches[2]][$matches[1]]=$test_values[$col['v']];
                        }
                        elseif (isset($col['v'])){
                            $xls_values[$matches[2]][$matches[1]]=$col['v'];

                        }
                    }
                }
            }
        }
    }
    $f->close();

    $test_data = array();
    $current_test = '';
    foreach($xls_values as $val){
        if (isset($val['A']) && preg_match('/Задача [A-ZА-Я]/', $val['A'])){
            $current_test = $val['A'];
            $test_data[$current_test] = array();
        }
        else {
            // Модификация данных часть решения задачи
            // Вручную проставил номера и подчистил ответы для теста A
            // (местами возможно excel намутил что-то)
            $test_data[$current_test][$val['A']] = array();
            $test_data[$current_test][$val['A']]['inp'] = $val['B'];
            $test_data[$current_test][$val['A']]['out'] = $val['C'] ?? '';
        }
    }
    // print_r($test_data);
?>