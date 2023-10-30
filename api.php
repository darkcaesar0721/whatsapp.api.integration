<?php

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/vendor/ultramsg/whatsapp-php-sdk/ultramsg.class.php';

error_reporting(E_ERROR);

$token = 'vepszz8ae514rgkf';
$instance_id = 'instance54684';

// This is a shared google sheet url.
$url = 'https://docs.google.com/spreadsheets/d/1fqf29GzyNGbdkL_5HMEDbMEarU4bwagzshXpcRGlcqg/edit?pli=1#gid=0';

$url_array = parse_url($url);
$path_array = explode("/", $url_array["path"]);

$spreadsheetId = $path_array[3];

$google_sheet_client = new \Google_Client();
$google_sheet_client->setApplicationName('Google Sheets and PHP');
$google_sheet_client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$google_sheet_client->setAccessType('offline');
$google_sheet_client->setAuthConfig(__DIR__ . '/credentials.json');
$google_sheet_service = new Google_Service_Sheets($google_sheet_client);

$spreadSheet = $google_sheet_service->spreadsheets->get($spreadsheetId);
$sheets = $spreadSheet->getSheets();

$cur_sheet = [];
foreach($sheets as $sheet) {
    $sheetId = $sheet['properties']['sheetId'];

    $pos = strpos($url, "gid=" . $sheetId);

    if($pos) {
        $cur_sheet = $sheet;
        break;
    }
}

$range = $cur_sheet['properties']['title']; // the service will detect the last row of this sheet
$response = $google_sheet_service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

$class = 'ultramsg\WhatsAppApi';
$whatsapp_client = new $class($token, $instance_id);

foreach ($values as $row) {
    if ($row[0] == 'Group_id') continue;

    $group_id = $row[0];
    $message = $row[1];

    $api = $whatsapp_client->sendChatMessage($group_id, $message);
}


