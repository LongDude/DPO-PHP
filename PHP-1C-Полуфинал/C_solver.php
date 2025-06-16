<?php

function solver(array $in_data){
    $fp = 0; // итератор для текущей строки

    $sections = [];
    $sections_index_map = [];
    for($sec_count = $in_data[$fp++];$sec_count > 0; $sec_count--){
        $section = $in_data[$fp++]; 
        $sections_index_map[$section] = count($sections_index_map);
        $sections[$section] = 0;
    }
    
    // Массив действий совершенных пользователем
    $user_actions = [];
    for($action_count = $in_data[$fp++];$action_count > 0; $action_count--){
        [$user, $action] = explode(' ', $in_data[$fp++]);
        if (!isset($user_actions[$user])){
            $user_actions[$user] = array_fill(0, count($sections), False);
        }
        if (!$user_actions[$user][$sections_index_map[$action]]){
            $user_actions[$user][$sections_index_map[$action]] = True;
            $sections[$action]++;
        }
    }
    print_r($sections);

    // Координаты по y начала последующих секций
    // Начало первой секции зафиксировано на (20,20)px
    $sections_width_px = [];
    $section_start_y = [];
    foreach($sections as $action_c){
        $width = (int)(($action_c / count($user_actions)) * 560);
        $sections_width_px[] =  $width;
        $section_start_y[] = 580 - $width;
    }
    print_r($sections_width_px);
    print_r($section_start_y);


    // Генерация изображения
    $resimage = imagecreate(600, 600);
    $back_color = imagecolorallocate($resimage, 255, 255, 255);
    $COLORS = [
        20 => imagecolorallocate($resimage, 0xff, 0x00, 0x00),
        100 => imagecolorallocate($resimage, 0xff, 0xb6, 0x00),
        180 => imagecolorallocate($resimage, 0x92, 0xff, 0x00),
        260 => imagecolorallocate($resimage, 0x00, 0xff, 0x24),
        340 => imagecolorallocate($resimage, 0x00, 0xff, 0xda),
        420 => imagecolorallocate($resimage, 0x00, 0x6e, 0xff),
        500 => imagecolorallocate($resimage, 0x48, 0x00, 0xff)
    ];
   
    for ($i = 0; $i < count($sections_width_px); $i++){
        // 1. Определяем цвет текущей секции цвет с максимальной глубиной, меньшей чем глубина секции
        $selected_col = 0;
        foreach ($COLORS as $h => $color){
            if ($h <= $section_start_y[$i]) {
                $selected_col = $color;
            }
            else {
                break;
            }
        }
        
        // 2. Заполняем секцию
        $padding = (600 - $sections_width_px[$i]) / 2;
            // Промежуточные трапеции
        imagefilledpolygon($resimage, [
            $padding, $section_start_y[$i],
            $padding + $sections_width_px[$i], $section_start_y[$i],
            300, 580 
        ], $selected_col);
    }

    return $resimage;
}


$test_num = readline("Input test number: ");
$data = file("./tests/C/$test_num.dat");
imagepng(solver($data), "./res.png");
echo "Find result in ./res.png";