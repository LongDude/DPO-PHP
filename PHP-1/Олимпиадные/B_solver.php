<?php
    $input_lines = file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $out_file = fopen('output.txt', 'w');

    // Для каждого IP адреса
    foreach ($input_lines as $line) {
        $line = trim($line);
        $elements = explode(':', $line); // Выделяем блоки
        $el_c = count($elements); // Подсчитываем количество блоков

        // При таком разбиении по символам : получаются массивы следующих видов:
        // ::0000:0000 => ["", "", 0000, 0000]
        // 0000::0000 => [0000, "", 0000]
        // 0000:0000:: => [0000, 0000, "", ""]
        // По пустым элементам определяем положение ::

        $decoded = array();
        if ($elements[0] === ''){ // Первый вид
            // Добавляем в массив срез [2:], остаток до 8 блоков добиваем 0000 слева
            $decoded = array_slice($elements, 2);
            $decoded = array_pad($decoded, -8, "0000");
            if ($decoded[7] === ''){ // Для адресов вида '::' три пустых блока
                $decoded[7] = "0000";
            }
        }
        // Для адресов вида 0000:0000::
        elseif ($elements[$el_c - 1] === ''){
            $decoded = array_slice($elements,0, $el_c - 2);
            $decoded = array_pad($decoded, 8, "0000");    
        }
        // Если пропуск в середине массива
        else {
            foreach($elements as $el){
                // Добиваем нехватающие блоки нулевыми
                if ($el === ''){
                    for ($i = $el_c - 1; $i < 8; $i++){
                        $decoded[] = "0000";
                    }
                }
                // Обычные блоки копируем
                else {
                    $decoded[] = $el;
                }
            }
        }

        // Постепенно записываем итоговый адрес в вывод
        // Каждый блок str_pad дополняется до 4 цифр нулями слева
        for ($i = 0; $i < 8; $i++){
            fwrite($out_file, str_pad($decoded[$i], 4, '0', STR_PAD_LEFT));
            // Первые 7 блоков оканчиваются на :, после последнего перевод каретки
            if ($i < 7){
                fwrite($out_file, ":");
            }
            else
            {
                fwrite($out_file, "\n");
            }
        }
    }

    
    fclose($out_file);
?>