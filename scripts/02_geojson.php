<?php
require __DIR__ . '/vendor/autoload.php';
$basePath = dirname(__DIR__);

$targetPath = $basePath . '/docs/geojson';
if (!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
}

$fc = [
    'type' => 'FeatureCollection',
    'features' => [],
];
foreach (glob($basePath . '/raw/json/*/*/*.json') as $jsonFile) {
    $json = json_decode(file_get_contents($jsonFile), true);
    if (isset($json['fee']['regisT_FEE_NAME']) && false === strpos($json['fee']['regisT_FEE_NAME'], '院所未提供資料')) {
        $f = [
            'type' => 'Feature',
            'properties' => [
                'id' => $json['hosP_ID'],
                'name' => $json['hosP_NAME'],
                'phone' => $json['hosptel'],
                'address' => $json['hosP_ADDR'],
                'type' => $json['hosP_CNT_TYPE'],
                'normal' => intval(preg_replace('/[^0-9]+/', '', $json['fee']['regisT_FEE_NAME'])),
                'emergency' => intval(preg_replace('/[^0-9]+/', '', $json['fee']['eM_REGIST_FEE_NAME'])),
                'note' => str_replace('醫事機構備註：', '', $json['fee']['feE_REMARK_NAME']),
                'service_periods' => '',
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    $json['longitude'],
                    $json['latitude'],
                ],
            ],
        ];
        foreach ($json['srV_INFO']['srv_Time'] as $lv1) {
            foreach ($lv1 as $lv2) {
                $f['properties']['service_periods'] .= $lv2['yn'];
            }
        }
        $fc['features'][] = $f;
    }
}
file_put_contents($targetPath . '/points.json', json_encode($fc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
