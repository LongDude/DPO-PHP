    <?php
    function djcstra($graph, $a, $b): int{
        // Рассчитывает самый короткий путь A -> B

        $d = array(); // массив кратчайших путей
        $pi = array(); // Массив "предшественников"
        $Q = new SplPriorityQueue(); // Очередь неоптимизированных узлов

        foreach ($graph as $v => $adj) {
            $d[$v] = INF; // устанавливаем изначальные расстояния как бесконечность
            $pi[$v] = null; // никаких узлов позади нет
            foreach ($adj as $w => $cost) {
            // воспользуемся ценой связи как приоритетом
            $Q->insert($w, $cost);
            }
        }

        // Начальная дистанция на стартовом узле - 0
        $d[$a] = 0;

        while (!$Q->isEmpty()){
            $u = $Q->extract();
            if (!empty($graph[$u])) {
                // пройдемся по всем соседним узлам
                foreach ($graph[$u] as $v => $cost) {
                // установим новую длину пути для соседнего узла
                $alt = $d[$u] + $cost;
                // если он оказался короче
                if ($alt < $d[$v]) {
                    $d[$v] = $alt; // update minimum length to vertex установим как минимальное расстояние до этого узла
                    $pi[$v] = $u;  // добавим соседа как предшествующий этому узла
                }
                }
            }
        }

        // теперь мы можем найти минимальный путь
        // используя обратный проход
        $S = new SplStack(); // кратчайший путь как стек
        $u = $b;
        $dist = 0;
        // проход от целевого узла до стартового
        while (isset($pi[$u]) && $pi[$u]) {
        $S->push($u);
        $dist += $graph[$u][$pi[$u]]; // добавим дистанцию для предшествующих
        $u = $pi[$u];
        }
        print_r($S);
        return $S->isEmpty() ? -1 : $dist;
    }

    // Чтение входных данный
    $input_lines = fopen('input.txt', 'r');
    $out_file = fopen('output.txt', 'w');

    // Параметры размера сети
    list($n, $m) = explode(' ', $input_lines.readline());

    // Граф сети
    $graph = array_fill(0, $n, array());
    for ($i = 0; $i < $m; $i++){
        list($a, $b, $l) = explode(' ', $input_lines.readline());

        $graph[$a] += [$b => $l];
        $graph[$b] += [$a => $l];
    }
    print_r($graph);

    // Запросы
    for ($i = 0; $i < $m; $i ++){
        list($a, $b, $r) = explode(' ', $input_lines.readline());
        
        switch($r){
            case '?': {
                $l = djcstra($graph, $a, $b);
                fwrite($out_file, "$l\n");
            }
            case '-1': {
                unset($graph[$a][$b]);
                unset($graph[$b][$a]);
            }
            default: {
                // Для положительных чисел
                $graph[$a][$b] = $l;
                $graph[$b][$a] = $l;
            }
        }
    }

    // Завершение возврата результата
    fclose($in_file);
    fclose($out_file);
?>