<?php
    function _encode_color($color){
        $color = $color[0];
        // Сокращенная запись цвета
        if ($color[1] == $color[2] && $color[3] == $color[4] && $color[5] == $color[6]){
            $color = "#" . $color[1] . $color[3] . $color[5];
        }

        // Псевдонимы для цветовых кодов
        return match ($color){
            "#CD853F" => "peru",
            "#FFC0CB" => "pink",
            "#DDA0DD" => "plum",
            "#F00" => "red",
            "#FFFAFA" => "snow",
            "#D2B48C" => "tan",
            default => $color
        };
    }
    

    function _parse_block($block):string{
        $block = $block[0];
        // Проверяет отдельно блок параметров на margin, padding
        $block = _parse_param($block, "margin");
        $block = _parse_param($block, "padding");
        return $block;
    }

    function _parse_param($block, $param):string{
        // 
        $matches = array();
        $pattern = "/(?:($param-top):(.+);)|(?:($param-right):(.+);)|(?:($param-bottom):(.+);)|(?:($param-left):(.+);)/U";
        
        // Ищем отдельные параметры
        preg_match_all($pattern, $block, $matches, PREG_UNMATCHED_AS_NULL | PREG_SET_ORDER);
        $values = array(null, null, null, null);
        
        $first_match = null;
        foreach ($matches as $match){
            // Перебираем контрольные группы (пары параметр:значение)
            for($i = 1; $i < 5; $i++){
                // Если значение есть (параметр существует в блоке)
                if (isset($match[2*$i])){
                    $values[$i-1] = $match[2*$i];
                    if (!isset($first_match)){
                        $first_match = $match[2*$i - 1];
                    }
                }
            }
        }

        // Заменим первое вхождение параметра на объединение
        $first_pat = "/$first_match:.+;/U";
        $repl = "$param:"; 
        // Указаны все 4 частных параметра
        if (isset($values[0]) && isset($values[1]) && isset($values[2]) && isset($values[3])){
            // Все аргументы равны
            if ($values[0] == $values[1] && $values[0] == $values[2] && $values[0] == $values[3]){
                $repl .= $values[0] . ";";

            // Аргументы попарно равны: вертикаль горизонталь
            } elseif ($values[0] == $values[2] && $values[1] == $values[3]){
                $repl .= $values[0] . " " . $values[1] . ";";
            
            // Аргументы горизонтали равны
            } elseif ($values[1] == $values[3]){
                $repl .= $values[0] . " " . $values[1] . " " . $values[2] . ";";
            } else {
                $repl .= implode(" ",$values) . ";";
            }
            $block = preg_replace($first_pat, $repl, $block);
            $block = preg_replace($pattern, '', $block);
        }

        return $block;
    }

    function solve_d(string $input): string {
        // Убираем комментарии
        $result = preg_replace("/(\/\*[^\/]+\*\/)/", '', $input);

        // Убираем лишние символы
        $result = preg_replace("/((?<=\W)\s+)|(\s+(?={))|((?<=(?: 0)|(?::0))px)/", "", $result);
        
        // Убираем пустые стили 
        $result = preg_replace("/([^{}]+{})/U", "", $result);
        
        // Сокращение цветов
        $result = preg_replace_callback("/#\w{6}/", "_encode_color", $result);

        // Изолируем отдельный блок для поиска параметров
        $result = preg_replace_callback("/{.+}/U", "_parse_block", $result);

        // PATCH: удаляем ; в конце блока стилей
        $result = preg_replace('/;(?=})/', '', $result);
        return $result;

        // Примечание: в 5 ответе padding перенес .tovar-detail__row-1 .ui-product-elements__product-equal a i.fa
        // после margin-right: 15px т.к. по условию оно должно было там остаться - ни о каких сортировках речи не шло
        // Аналогично для теста 8: padding не должен был поменять положение в css, но в ответе сдвинулся
        // Тест 9: снова сместили padding (который сохраняет начальное значение)
    }
?>