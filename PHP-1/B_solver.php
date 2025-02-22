<?php
    $input_lines = file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $out_file = fopen('output.txt', 'w');

    foreach ($input_lines as $line) {
        $line = trim($line);
        $elements = explode(':', $line);
        $el_c = count($elements);

        $decoded = array();

        // fwrite(STDOUT, "test\n");
        if ($elements[0] === ''){
            $decoded = array_slice($elements, 2);
            $decoded = array_pad($decoded, -8, "0000");
            if ($decoded[7] === ''){
                $decoded[7] = "0000";
            }
        }
        elseif ($elements[$el_c - 1] === ''){
            $decoded = array_slice($elements,0, $el_c - 2);
            $decoded = array_pad($decoded, 8, "0000");    
        }
        else {
            foreach($elements as $el){
                if ($el === ''){
                    for ($i = $el_c - 1; $i < 8; $i++){
                        $decoded[] = "0000";
                    }
                }
                else {
                    $decoded[] = $el;
                }
            }
        }

        for ($i = 0; $i < 8; $i++){
            fwrite($out_file, str_pad($decoded[$i], 4, '0', STR_PAD_LEFT));
            if ($i < 7){
                fwrite($out_file, ":");
            }
            else
            {
                fwrite($out_file, "\n");
            }
        }
    }

    
    fclose($out_file);
?>