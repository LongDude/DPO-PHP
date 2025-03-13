<?php
    function compare_items($a, $b){
        if ($a[1] > $b[1]){
            return 1;
        }
        else {
            return -1;
        }
    }
    function solve_b(string $input): string {
        $result = "";
        // Собираем массив товаров
        $shop_items = array();
        foreach (explode("\n", $input) as $item) {
            // Первый индекс не нужен как правило
            list($blank, $name, $lkey, $blank) = explode(" ", $item);
            
            $shop_items[] = array($name, $lkey);
        }

        // Сортируем по возрастанию времени входа
        usort($shop_items, "compare_items");

        $currentlevel = 0;
        $p = 0;
        $curr_item = 0;
        while ($curr_item < count($shop_items)){
            
            // Перебираем все предметы в магазине
            $p++;
            $currentlevel++;

            while($shop_items[$curr_item][1] > $p){
                $currentlevel--;
                $p++;
            }

            for ($i = 1; $i < $currentlevel; $i++){
                $result .= '-';
            }

            $result .= $shop_items[$curr_item][0] . "\n";
            $curr_item++;
        }

        return $result;
    }
?>