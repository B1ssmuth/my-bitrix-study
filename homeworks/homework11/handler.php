<?php
/**
 * ДЗ: Локальное REST приложение
 * Обработчик создания дела, который обновляет дату в Контакте
 */

// ВАЖНО: Здесь теперь строго HTTPS!
define('B24_WEBHOOK_URL', 'https://ce255660.tw1.ru/rest/1/vw65vprt35ii6b2h/');
define('CONTACT_FIELD_DATE', 'UF_CRM_1783519709');

$request = $_REQUEST;
$logFile = __DIR__ . '/webhook_log.txt';

// 1. Логируем старт и входящий запрос
file_put_contents($logFile, date('Y-m-d H:i:s') . " - СТАРТ. Входящий запрос: " . print_r($request, true) . "\n", FILE_APPEND);

if (isset($request['event']) && $request['event'] === 'ONCRMACTIVITYADD' && !empty($request['data']['FIELDS']['ID'])) {
    
    $activityId = intval($request['data']['FIELDS']['ID']);

    // 2. Получаем данные о созданном деле по HTTPS
    $activityData = executeREST('crm.activity.get', ['id' => $activityId]);

    // Логируем ответ от Битрикса по делу
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Данные дела $activityId: " . print_r($activityData, true) . "\n", FILE_APPEND);

    if (!empty($activityData['result'])) {
        $activity = $activityData['result'];
        $contactId = null;

        // Ищем ID контакта (3 — системный код сущности Контакт)
        if (isset($activity['OWNER_TYPE_ID']) && $activity['OWNER_TYPE_ID'] == 3) {
            $contactId = intval($activity['OWNER_ID']);
        }
        elseif (!empty($activity['BINDINGS'])) {
            foreach ($activity['BINDINGS'] as $binding) {
                if ($binding['OWNER_TYPE_ID'] == 3) {
                    $contactId = intval($binding['OWNER_ID']);
                    break;
                }
            }
        }
        if (!$contactId && !empty($activity['COMMUNICATIONS'])) {
            foreach ($activity['COMMUNICATIONS'] as $comm) {
                if ($comm['ENTITY_TYPE_ID'] == 3) {
                    $contactId = intval($comm['ENTITY_ID']);
                    break;
                }
            }
        }

        // 3. Обновляем контакт
        if ($contactId > 0) {
            $currentDate = date('c'); 

            $updateResult = executeREST('crm.contact.update', [
                'id' => $contactId,
                'fields' => [
                    CONTACT_FIELD_DATE => $currentDate
                ]
            ]);

            file_put_contents($logFile, date('Y-m-d H:i:s') . " - УСПЕХ! Контакт $contactId успешно обновлен: " . print_r($updateResult, true) . "\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - ОШИБКА: Контакт не найден в массивах дела №$activityId.\n", FILE_APPEND);
        }
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - ОШИБКА crm.activity.get: Пустой ответ или ошибка доступа.\n", FILE_APPEND);
    }
}

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