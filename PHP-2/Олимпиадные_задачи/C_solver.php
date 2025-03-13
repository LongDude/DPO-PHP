<?php
    function solve_c(string $input): string {
        $result = "";
        $summary = 0; // Общий бюджет рекламы
        $banners = array();
        foreach (explode("\n", $input) as $banner) {
            // Получаем id и стоимость каждого баннера
            list($id, $cost) = explode(" ", $banner);
            $cost = (int)$cost;
            $summary += $cost;
            $banners[] = array($id, $cost);
        }

        foreach ($banners as $banner) {
            // Выводим имя баннера и пропорцию от стоимости
            $result .= $banner[0] . ' ' . sprintf('%.06f', $banner[1] / $summary) . "\n"; 
        }

        return $result;
    }
?>