<?php

    function encode_bin(int  $int): string{
        $pack = [0,0,0,0];
        $pack[3] = $int % 256; $int >>= 8; 
        $pack[2] = $int % 256; $int >>= 8; 
        $pack[1] = $int % 256; $int >>= 8;
        $pack[0] = $int; 
        return "$pack[0].$pack[1].$pack[2].$pack[3]";
    }

    [$n, $k] = explode(' ', readline());   
    $network = array();
    for ($i = 0; $i < $n; $i++) {
        $ip_stream = explode('.', readline());
        $network[] = ($ip_stream[0] << 24) | ($ip_stream[1] << 16)| ($ip_stream[2] << 8)| ($ip_stream[3]);
    }
    var_dump($network);
    sort($network);

    if ($n == $k){
        print("255.255.255.255\n");
    }

    $mask = 0xFFFFFFFF;
    $unmasked = 0xFF;
    for ($i = 0; $i < 24; $i++){
        $subnets = 1;
        for ($nid = 0; $nid < count($network); $nid++) {
            $network[$nid] >>= 1;
            if ($nid > 0 && $network[$nid-1] != $network[$nid]){
                $subnets++;
            }
        }

        if ($subnets == $k){
            break;
        }

        $unmasked <<= 1;
        $unmasked |= 1;
    }
    $mask ^= $unmasked;
    print(encode_bin($mask)."\n");