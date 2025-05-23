<?php

$urlmap = array();
// Чтение массива документов
$line = trim(readline());
while (!empty($line)) {
    [$id, $url, $parent_id, $time] = explode(";", $line);
    $urlmap[$id] = array("url"=>$url, "parent"=>$parent_id, "time"=>$time);

    // Обновляем родительское время редактирования
    while ($parent_id != 0){
        if ($urlmap[$parent_id]["time"] < $time){
            $urlmap[$parent_id]["time"] = $time;
            $parent_id = $urlmap[$parent_id]["parent"];
        } else {
            // Дальнейшее обновление не нужно
            break;
        }
    }

    $line = trim(readline());
}

echo <<< END
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">\n
END;

// Выглядит грязно, но позволяет расположить ссылки по порядку
$urlmap_ids = array_keys($urlmap);
sort($urlmap_ids);

foreach ($urlmap_ids as $url_id) {
    // Обрабатываем данные и время для каждой ссылки
    $url = $urlmap[$url_id];
    $url_link = $url["url"];
    $timeobj = new DateTimeImmutable()::createFromTimestamp($url["time"]);
    $timeobj->setTimezone(new DateTimeZone("+0300"));
    $url_timestamp = $timeobj->format(DateTimeImmutable::ISO8601);

    // Вывод
    echo <<< END
        <url>
            <loc>$url_link<loc>
            <lastmod>$url_timestamp<lastmod>
        </url>\n
    END;
}

echo <<< END
</urlset>\n
END;

