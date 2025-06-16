<?php

function solve(SimpleXMLElement $in_products, SimpleXMLElement $in_sections): string{
    // Собираем структуру разделов для каталога
    $sections_order_table = [];
    $sections_map = [];
    for ($in_sections->rewind(); $in_sections->valid(); $in_sections->next()){
        $section_fields = [];
        foreach($in_sections->getChildren() as $field_name => $field_data){
            $section_fields[$field_name] = (string)$field_data;
        }

        $sections_order_table[] = $section_fields['Ид'];
        $sections_map[$section_fields['Ид']]['Name'] = $section_fields['Наименование'];
        $sections_map[$section_fields['Ид']]['Products'] = [];
    }

    // print_r($sections_order_table);
    // print_r($sections_map);

    // Собираем информацию о товарах и добавляем в структуру каталога
    for($in_products->rewind(); $in_products->valid(); $in_products->next()){
        $products_fields = [];
        foreach($in_products->getChildren() as $field_name => $field_data){
            // Извлекаем поля из записи о продукте
            if ($field_name != 'Разделы'){
                $products_fields[$field_name] = (string)$field_data;
            }
            else {
                // Добавляем продукт для соотетствующих секций
                foreach($field_data as $product_section){
                    $sections_map[(string)$product_section]['Products'][] = $products_fields;
                }
            }
        }
    };
    // print_r($sections_map);

    // Формируем xml файл ответа
    $result = new XMLWriter()::toMemory();
    $result->setIndent(True);
    $result->setIndentString('    ');
    $result->startDocument("1.0", 'UTF-8');
    $result->startElement('ЭлементыКаталога');
    $result->startElement('Разделы');
    foreach($sections_order_table as $section_id){
        $result->startElement('Раздел');
        $section = $sections_map[$section_id];
        $result->writeElement('Ид', $section_id);
        $result->writeElement('Наименование', $section['Name']);
        $result->startElement('Товары');
        foreach($section['Products'] as $product){
            $result->startElement('Товар');
            foreach($product as $key => $data){
                $result->writeElement($key, $data);
            }
            $result->endElement();
        }
        $result->endElement();
        $result->endElement();
    }
    $result->endElement();
    $result->endElement();
    $result->endDocument();
    $xml = $result->outputMemory();
    return $xml;
}

// Тестируем результат
$test_dir = scandir("./tests/B");
$task_list = array();

// Загружаем все тесты для данной задачи
// Перебираем файлы в директории
foreach($test_dir as $filename){
    // Отделяем файлы с условиями .dat от файлов с ответами .ans
    $matches = [];
    $m = preg_match("/(\d+)_(products.xml|result.xml|sections.xml)/", $filename, $matches);
    if ($m){
        // Для каждого текста в массиве сохраняем соотвествующий файл
        if (!array_key_exists($matches[1], $task_list)){
            $task_list += [$matches[1] => array()]; // Инициализация ключа
        }
        $task_list[$matches[1]] += [$matches[2] => "./tests/B/{$filename}"];
    }
}


// print_r($task_data);
// Перебираем тесты
$assert = true; // До первого сбоя
foreach ($task_list as $task_number => $task_file){
    echo "Test [{$task_number}]...";

    // Достаем фактические и теоретические результаты
    $products_xml = simplexml_load_file($task_file['products.xml']);
    $sections_xml = simplexml_load_file($task_file['sections.xml']);
    $expected_xml = file($task_file['result.xml'], FILE_IGNORE_NEW_LINES);
    
    $result = explode("\n", solve($products_xml, $sections_xml));
    unset($result[count($result) - 1]); // Последний элемент как правило пустая строка

    // Если массивы не идентичны, то программа выдала неверный результат 
    if ($result != $expected_xml){
        echo "Wrong!\n";
        foreach ($result as $i => $res_str) {
            if ($i >= count($expected_xml) || $res_str != $expected_xml[$i]){
                echo "Line " . $i+1 . "\n";
                echo "Expected:" . ($i < count($expected_xml) ? $expected_xml[$i] : "NONE") . "\n";
                echo "Got     :" . $result[$i] . "\n";
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