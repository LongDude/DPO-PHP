<?php
    header('Content-type: application/json;charset=utf-8');

function callApi($address) {
    $address = urlencode($_GET['partial_adress']);   
    $apiUrl = "https://geocode-maps.yandex.ru/1.x/?format=json&apikey=".$_ENV['API_KEY']."&lang=ru_RU&geocode=$address&results=1";

    $response = file_get_contents($apiUrl);
    if ($response === FALSE) {
        return ['error' => 'Ошибка связи с Yandex Geocoder API'];
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Не удалось распарсить JSON'];
    }
    
    return $data;
}

function extractData($yandexData) {
    if (!isset($yandexData['response']['GeoObjectCollection']['featureMember'][0])) {
        return ['error' => 'Адрес не найден'];
    }
    
    $feature = $yandexData['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];
    $pos = explode(' ', $feature['Point']['pos']);
    $longitude = $pos[0];
    $latitude = $pos[1];
    $fullAddress = $feature['metaDataProperty']['GeocoderMetaData']['text'];
   
    // Ищем метро из полученных адресов (если передавали адрес метро или та же улица)
    $metro = null;
    if (isset($feature['metaDataProperty']['GeocoderMetaData']['Address']['Components'])) {
        foreach ($feature['metaDataProperty']['GeocoderMetaData']['Address']['Components'] as $component) {
            if ($component['kind'] === 'metro') {
                $metro = $component['name'];
                break;
            }
        }
    }
    
    // Иначе запрашиваем API
    if (!$metro) {
        $metroData = findClosestMetro($longitude, $latitude);
    }
    
    return [
        'full_address' => $fullAddress,
        'longitude' => $longitude,
        'latitude' => $latitude,
        'closestMetro' => $metroData[0],
        'metroLongitude' => $metroData[1],
        'metroLatitude' => $metroData[2],
    ];
}

function findClosestMetro($longitude, $latitude) {
    $apiUrl = "https://geocode-maps.yandex.ru/v1/?apikey=".$_ENV['API_KEY']."&geocode=$longitude,$latitude&lang=ru_RU&kind=metro&format=json&results=1";
    $response = file_get_contents($apiUrl);
    
    if ($response === FALSE) {
        return ['Неизвестно: Ошибка запроса', '-', '-'];
    }
    
    $data = json_decode($response, true)['response']['GeoObjectCollection'];
    
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['featureMember'][0])) {
        return ['Не найдено', '-', '-'];
    }
    $feature = $data['featureMember'][0]['GeoObject'];
    $pos = explode(' ', $feature['Point']['pos']);
    

    $metroData = [
        $feature['metaDataProperty']['GeocoderMetaData']['text'],
        $pos[0],
        $pos[1],
    ];
    return $metroData;
}

// Непосредственно запрос
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['partial_adress'])) {

    $rawYandexData = callApi($_GET['partial_adress']);
    
    if (isset($yandexData['error'])) {
        http_response_code(500);
        $result = $rawYandexData;
    } else {
        $result = extractData($rawYandexData);
    }
} else {
    http_response_code(403);
    $result = null;
}

http_response_code(200);
echo json_encode($result);
?>
