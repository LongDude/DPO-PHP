<?php
    $contents = file_get_contents("./sNk6ntM4.html");
    $debug_output = true;

    // Заменяем исходные ссылки на новые по шаблону
    $contents = preg_replace_callback("/http:\/\/asozd\.duma\.gov\.ru\/main\.nsf\/\(Spravka\)\?OpenAgent&RN=([0-9-]+)&(\d+)/", function($m) {
        // Отладка для проверки работы подстановки
        global $debug_output;
        if ($debug_output) {
            print_r($m);
            echo "http://sozd.parlament.gov.ru/bill/" . $m[1] . "\n";
        }
        
        return "http://sozd.parlament.gov.ru/bill/" . $m[1];
    }, $contents);

    print_r($contents);
