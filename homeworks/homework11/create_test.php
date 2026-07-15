<?php
$webhookUrl = 'https://ce255660.tw1.ru/rest/1/vw65vprt35ii6b2h/crm.activity.add.json';

$contactId = 1; 

$queryData = http_build_query([
    'fields' => [
        'OWNER_TYPE_ID' => 3,
        'OWNER_ID' => $contactId,
        'TYPE_ID' => 2,
        'SUBJECT' => 'Тестовое дело из REST API (Хак)',
        'DIRECTION' => 2,
        'COMPLETED' => 'N',
        'COMMUNICATIONS' => [
            [
                'ENTITY_ID' => $contactId,
                'ENTITY_TYPE_ID' => 3,
                'VALUE' => '+7 (999) 000-00-00'
            ]
        ]
    ]
]);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $webhookUrl,
    CURLOPT_POSTFIELDS => $queryData,
]);

$result = curl_exec($curl);
curl_close($curl);

echo "<h1>Команда отправлена!</h1>";
echo "<p>Ответ Битрикса на создание дела:</p>";
echo "<pre>" . print_r(json_decode($result, true), true) . "</pre>";
echo "<p>Если выше написано result => ID, значит дело успешно создано!<br>Теперь иди проверяй webhook_log.txt и карточку Контакта!</p>";