<?php
    // открываем файлы
    $in_file = fopen("input.txt", 'r');
    $out_file = fopen('output.txt','w');

    // Получаем количество ставок и сами ставки
    $bets_count = trim(fgets($in_file));
    $bets = [];
    for ($i = 0; $i < $bets_count; $i ++) {
        $bet = explode(' ', trim(fgets($in_file)));
        $bets += [$bet[0] => [$bet[1], $bet[2]]];
    }

    // получаем количество игр и сами игры
    $games_count = trim(fgets($in_file));
    $games = [];
    for ($i = 0; $i < $games_count; $i ++) {
        $game_result = explode(' ', trim(fgets($in_file)));
        $games += [$game_result[0] => [$game_result[1], $game_result[2], $game_result[3], $game_result[4]]];
    }

    // Считаем суммарный выигрыш со ставок, ориентируясь на исход игр
    $win_sum = 0;
    foreach ($bets as $bet_num => $bet_info) {
        $win_sum -= $bet_info[0]; // Забираем сумму на ставку
        
        // Если игра состоялась и мы выиграли
        if (array_key_exists($bet_num, $games) && $games[$bet_num][3] == $bet_info[1]){
            $game_result = $games[$bet_num];
            $coeff = match ($bet_info[1]){ // Определяем коэффициент
                "L" => $game_result[0],
                "R" => $game_result[1],
                "D" => $game_result[2],
            };
            // небольшоая отладка
            // echo $bet_num . ' ' . implode($bet_info) . "|" . implode($game_result) . "|" . $coeff .'\n';
            $win_sum += $bet_info[0] * $coeff; // Получаем выигрыш с учетом коэффициента
        }
    }

    // Завершение: запись и закрытие файлов
    fwrite($out_file, "{$win_sum}");
    fclose($in_file);
    fclose($out_file);
?>