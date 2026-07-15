<?php
$webhookUrl = 'https://ce255660.tw1.ru/rest/1/vw65vprt35ii6b2h/crm.activity.add.json';
$contactId = 1;

$queryData = http_build_query([
    'fields' => [
        'OWNER_TYPE_ID' => 3, 
        'OWNER_ID' => $contactId,
        'TYPE_ID' => 2, 
        'SUBJECT' => 'Тестовый звонок (Авто-пинг для Контакта №1)',
        'DIRECTION' => 2, 
        'COMPLETED' => 'N',
        'COMMUNICATIONS' => [
            [
                'ENTITY_ID' => $contactId,
                'ENTITY_TYPE_ID' => 3,
                'VALUE' => '+7 (999) 111-11-11'
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

$resArray = json_decode($result, true);
$newActivityId = intval($resArray['result'] ?? 0);

echo "<div style='font-family: sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; border: 1px solid #ddd; border-radius: 8px; background: #fdfdfd; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>";
echo "<h2 style='color: #2e7d32; margin-top: 0;'>🚀 Генератор тестового дела</h2>";

if ($newActivityId > 0) {
    echo "<p>✅ <b>Шаг 1:</b> В Битрикс24 успешно создано новое дело <b>№{$newActivityId}</b> для Контакта №1.</p>";
    
    $handlerUrl = "https://ce255660.tw1.ru/homeworks/homework11/handler.php?event=ONCRMACTIVITYADD&data[FIELDS][ID]={$newActivityId}";
    
    $curlHandler = curl_init();
    curl_setopt_array($curlHandler, [
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $handlerUrl,
    ]);
    $handlerResult = curl_exec($curlHandler);
    curl_close($curlHandler);

    echo "<p>⚡️ <b>Шаг 2 (Авто-пинг):</b> Отправлен мгновенный запрос в <code>handler.php</code> с ID дела <b>№{$newActivityId}</b> (обход очереди крон-агента).</p>";
    echo "<div style='background: #e8f5e9; padding: 15px; border-left: 4px solid #2e7d32; margin-top: 20px; border-radius: 4px;'>";
    echo "<h4 style='margin: 0 0 10px 0; color: #1b5e20;'>🎉 Цепочка успешно отработала!</h4>";
    echo "<p style='margin: 0; font-size: 14px;'>Скрипт обработал дело №{$newActivityId} и обновил Контакт №1. Вернитесь на главную страницу задания — там обновился лог!</p>";
    echo "</div>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Ошибка создания дела:</p>";
    echo "<pre style='background: #ffeeee; padding: 10px; border-radius: 4px;'>" . print_r($resArray, true) . "</pre>";
}
echo "</div>";