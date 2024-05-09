<?php
require __DIR__ . '/vendor/autoload.php';
$basePath = dirname(__DIR__);

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

$browser = new HttpBrowser(HttpClient::create());
$csvFile = $basePath . '/raw/list.csv';

if (!file_exists($csvFile)) {
    $json = json_decode(file_get_contents('https://data.gov.tw/api/v2/rest/dataset/39283'), true);

    foreach ($json['result']['distribution'] as $item) {
        if ($item['resourceFormat'] === 'CSV') {
            file_put_contents($csvFile, file_get_contents($item['resourceDownloadUrl']));
            break;
        }
    }
}

$rawPaths = [];
if (file_exists($csvFile)) {
    $fh = fopen($csvFile, 'r');
    $header = fgetcsv($fh);
    $header[0] = '醫事機構代碼';
    while ($line = fgetcsv($fh)) {
        $data = array_combine($header, $line);
        if (!isset($rawPaths[$data['縣市別代碼']])) {
            $rawPaths[$data['縣市別代碼']] = $basePath . '/raw/json/' . $data['縣市別代碼'];
            if (!file_exists($rawPaths[$data['縣市別代碼']])) {
                mkdir($rawPaths[$data['縣市別代碼']], 0777, true);
            }
        }
        $targetFile = $rawPaths[$data['縣市別代碼']] . '/' . $data['醫事機構代碼'] . '.json';
        if (!file_exists($targetFile)) {
            $browser->jsonRequest('POST', 'https://info.nhi.gov.tw/api/inae1000/inae1000s00/SQL300', [
                'C_HospID' => $data['醫事機構代碼'],
                'C_planType' => '',
            ]);
            $response = $browser->getResponse()->getContent();
            $detail = json_decode($response, true);
            if (!empty($detail[0])) {
                file_put_contents($targetFile, json_encode($detail[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
        }
    }
}
