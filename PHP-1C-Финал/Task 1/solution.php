<?php

    $line = readline();
    $matches = array();

    // Разбиваем строку на элементы
    preg_match('/^(https?)([a-z0-9-]+)(ru|com)([a-z0-9-]*)$/', $line, $matches);

    // Вывод
    print("$matches[1]://$matches[2].$matches[3]");
    if (strlen($matches[4]) > 0){
        print("/$matches[4]");
    }
    print("\n");