<?php
    function solve_a(string $input): string {
        $result = "";
        $lines = explode("\n", $input);
        $banners = array();

        // Считываем построчно показы
        foreach ($lines as $line) {
            // Не работает разделение по табуляции
            list($id, $timestring) = preg_split("/(?<=\w{8}) +/", $line);
            
            $timestamp = strtotime($timestring); // Временная метка в виде числа
            // Количество показов баннера
            // Заодно инициализируем новые записи
            $banners[$id]['count'] = ($banners[$id]['count'] ?? 0) + 1;
            $banners[$id]['last_time'] = max($banners[$id]['last_time'] ?? 0, $timestamp);
        }

        foreach ($banners as $id => $banner){
            // Да и на выводе разбиение не по табуляции
            $result .= (string)$banner['count']." ".(string)$id." ".date('d.m.Y H:i:s', $banner['last_time'])."\n"; 
        }

        return $result;
    }
?>