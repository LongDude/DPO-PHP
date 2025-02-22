<?php
$a=readline("number arrray: ");      
$result = array();
$strings_array = explode(' ', $a);
$ressum = 0;


foreach ($strings_array as $number) {
    $result[] = (int) $number;
    $ressum += (int) $number;
}

$result['гном-ключекрад'] = 'гном-числокрад';

print_r($result);
print_r("Сумма: $ressum");
?>


