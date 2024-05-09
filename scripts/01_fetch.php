<?php
require __DIR__ . '/vendor/autoload.php';
$basePath = dirname(__DIR__);

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

// get files from https://info.nhi.gov.tw/INAE1000/INAE1000S00

$browser = new HttpBrowser(HttpClient::create());
$jsonFile = $basePath . '/raw/map.json';

if (!file_exists($jsonFile)) {
    $browser->jsonRequest('POST', 'https://info.nhi.gov.tw/api/inae1000/INAEmapS01/search', [
        'datatype' => '1',
        'keyword' => '',
        'km' => 20000,
        'lat' => 24.266060,
        'lng' => 119.913820,
    ]);
    $response = $browser->getResponse()->getContent();
    $map = json_decode($response, true);
    file_put_contents($jsonFile, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} else {
    $map = json_decode(file_get_contents($jsonFile), true);
}

$rawPaths = [];
if (!empty($map['mapdata'])) {
    foreach ($map['mapdata'] as $item) {
        $city = mb_substr($item['hosp_addr'], 0, 3, 'utf-8');
        $pathKey = "{$item['hosp_cnt_type']}/{$city}";
        if (!isset($rawPaths[$pathKey])) {
            $rawPaths[$pathKey] = $basePath . "/raw/json/{$item['hosp_cnt_type']}/{$city}";
            if (!file_exists($rawPaths[$pathKey])) {
                mkdir($rawPaths[$pathKey], 0777, true);
            }
        }
        $targetFile = $rawPaths[$pathKey] . '/' . $item['hosp_id'] . '.json';
        if (!file_exists($targetFile)) {
            echo "{$item['hosp_id']}\n";
            $browser->jsonRequest('POST', 'https://info.nhi.gov.tw/api/inae1000/inae1000s00/SQL300', [
                'C_HospID' => $item['hosp_id'],
                'C_planType' => '',
            ]);
            $response = $browser->getResponse()->getContent();
            $detail = json_decode($response, true);
            if (!empty($detail[0])) {
                $detail[0]['longitude'] = $item['wgs_lon'];
                $detail[0]['latitude'] = $item['wgs_lat'];
                file_put_contents($targetFile, json_encode($detail[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
        }
    }
}
