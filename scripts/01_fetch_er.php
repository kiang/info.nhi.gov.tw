<?php
require __DIR__ . '/vendor/autoload.php';
$basePath = dirname(__DIR__);

$targetPath = $basePath . '/docs/geojson';
if (!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
}


use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

// get files from https://info.nhi.gov.tw/INAE4000/INAE4001S02

$httpClient = HttpClient::create([
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'application/json, text/javascript, */*; q=0.01',
        'Accept-Language' => 'zh-TW,zh;q=0.9,en;q=0.8',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Referer' => 'https://info.nhi.gov.tw/INAE4000/INAE4001S02',
        'Origin' => 'https://info.nhi.gov.tw',
        'X-Requested-With' => 'XMLHttpRequest',
        'Connection' => 'keep-alive',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'same-origin',
        'Content-Type' => 'application/json',
    ]
]);

$browser = new HttpBrowser($httpClient);
$rawFile = $basePath . '/raw/er.json';

$browser->jsonRequest('POST', 'https://info.nhi.gov.tw/api/inae4000/inae4001s01/SQL0002', [
    'AREA_NO' => '',
    'CONT_TYPE' => '',
]);
$response = $browser->getResponse()->getContent();
$er = json_decode($response, true);
file_put_contents($rawFile, json_encode($er, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

/**
 *
            "hosP_ID": "0421040011",
            "hosP_NAME": "成大", //簡稱
            "areA_NO_N": "05", //縣市
            "conT_TYPE": "1", //院所層級/特約類別
            "inform": "Y", //是否已向119通報病床
            "waiT_SEE_CNT": "2", //看診
            "waiT_BED_CNT": "20", //推床
            "waiT_GENERAL_CNT": "52", //住院
            "waiT_ICU_CNT": "7", //加護病房
            "url": null,
            "txT_DATE": "2025-02-24T15:30:18"
 */
$infoRawPath = $basePath . '/raw/json';
$hospitalTypes = [
    '1' => '醫學中心',
    '2' => '區域醫院',
    '3' => '地區醫院',
];
$fc = [
    'type' => 'FeatureCollection',
    'features' => [],
];

foreach ($er['data'] as $hospital) {
    $hospitalType = $hospitalTypes[$hospital['conT_TYPE']];
    foreach (glob("{$infoRawPath}/{$hospitalType}/*/{$hospital['hosP_ID']}.json") as $file) {
        $info = json_decode(file_get_contents($file), true);
        $f = [
            'type' => 'Feature',
            'properties' => [
                'id' => $info['hosP_ID'],
                'name' => $info['hosP_NAME'],
                'phone' => $info['hosptel'],
                'address' => $info['hosP_ADDR'],
                'type' => $info['hosP_CNT_TYPE'],
                'inform' => $hospital['inform'],
                'wait_see' => $hospital['waiT_SEE_CNT'],
                'wait_bed' => $hospital['waiT_BED_CNT'],
                'wait_general' => $hospital['waiT_GENERAL_CNT'],
                'wait_icu' => $hospital['waiT_ICU_CNT'],
                'date' => $hospital['txT_DATE'],
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    $info['longitude'],
                    $info['latitude'],
                ],
            ],
        ];
        $fc['features'][] = $f;
    }
}

file_put_contents($targetPath . '/er.json', json_encode($fc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));