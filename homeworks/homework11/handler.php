<?php
/**
 * ДЗ: Локальное REST приложение
 * Обработчик создания дела, который обновляет дату в Контакте
 */

define('B24_WEBHOOK_URL', 'http://ce255660.tw1.ru/rest/1/vw65vprt35ii6b2h/');

define('CONTACT_FIELD_DATE', 'UF_CRM_1783519709');

$request = $_REQUEST;

file_put_contents(__DIR__ . '/webhook_log.txt', date('Y-m-d H:i:s') . " - " . print_r($request, true) . "\n", FILE_APPEND);

if (isset($request['event']) && $request['event'] === 'ONCRACTIVITYADD' && !empty($request['data']['FIELDS']['ID'])) {
    
    $activityId = intval($request['data']['FIELDS']['ID']);

    $activityData = executeREST('crm.activity.get', ['id' => $activityId]);

    if (!empty($activityData['result'])) {
        $activity = $activityData['result'];
        $contactId = null;

        if (!empty($activity['BINDINGS'])) {
            foreach ($activity['BINDINGS'] as $binding) {
                if ($binding['OWNER_TYPE_ID'] == 3) {
                    $contactId = intval($binding['OWNER_ID']);
                    break;
                }
            }
        }

        if ($contactId > 0) {
            $currentDate = date('c'); 

            $updateResult = executeREST('crm.contact.update', [
                'id' => $contactId,
                'fields' => [
                    CONTACT_FIELD_DATE => $currentDate
                ]
            ]);

            file_put_contents(__DIR__ . '/webhook_log.txt', "Контакт $contactId обновлен: " . print_r($updateResult, true) . "\n", FILE_APPEND);
        }
    }
}

/**
 * Вспомогательная функция для отправки REST запросов в Битрикс24
 */
function executeREST($method, $params) {
    $queryUrl = B24_WEBHOOK_URL . $method . '.json';
    $queryData = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ]);

    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, true);
}