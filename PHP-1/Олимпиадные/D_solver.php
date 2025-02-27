    <?php
    function djcstra($graph, $a, $b): int{
        // Рассчитывает самый короткий путь A -> B

        // Маска посещенных вершин
        $m = array_fill(0, count($graph), false); // Маска посещения вершин
        $d = array_fill(0, count($graph), INF); // Массив кратчайших путей
        $d[$a] = 0;
        
        $Q = new SplPriorityQueue(); // Очередь неоптимизированных узлов

        // наивысший приоритет для корня
        $Q->insert($a, INF); 
        while (!$Q->isEmpty()){
            // Выбираем неоптим. вершину с наименьшим путем до цели
            $u = $Q->extract();

            if ($m[$u]) continue; // Если для этого узла совершали оптимизацию
            $m[$u] = true;

            // Перебираем связанные с ней вершины
            foreach ($graph[$u] as $v => $cost){
                $alt = $d[$u] + $cost;
                // Уменьшаем стоимость если возможно
                if ($alt < $d[$v]){
                    $d[$v] = $alt;
                }
                // Добавляем в очередь оптимизации, если не посещали ранее
                if (!$m[$v]) {
                    $Q->insert($v, -$d[$v]);
                }
            }
        }

        return $m[$b] ? $d[$b] : -1;
    }

    // Чтение входных данный
    $in_file = fopen('input.txt', 'r');
    $out_file = fopen('output.txt', 'w');

    // Параметры размера сети
    list($n, $m) = explode(' ', trim(fgets($in_file)));

    // Граф сети
    $graph = array_fill(0, $n, array());
    for ($i = 0; $i < $m; $i++){
        list($a, $b, $l) = explode(' ', trim(fgets($in_file)));

        $graph[$a] += [$b => $l];
        $graph[$b] += [$a => $l];
    }
    print_r($graph);

    // Запросы
    $k = (int)fgets($in_file);
    for ($i = 0; $i < $k; $i ++){
        list($a, $b, $r) = explode(' ', trim(fgets($in_file)));
        
        switch($r){
            case '?': {
                $l = djcstra($graph, $a, $b);
                fwrite($out_file, "$l\n");
                break;
            }
            case '-1': {
                unset($graph[$a][$b]);
                unset($graph[$b][$a]);
                break;
            }
            default: {
                // Для положительных чисел
                $graph[$a][$b] = $r;
                $graph[$b][$a] = $r;
                break;
            }
        }
    }

    // Завершение возврата результата
    fclose($in_file);
    fclose($out_file);
?>