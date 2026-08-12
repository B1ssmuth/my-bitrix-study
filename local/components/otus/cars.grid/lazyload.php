<?php
// Отключаем лишнюю статистику для ускорения AJAX-запроса
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Принудительно отдаем браузеру JS и CSS, накопленные компонентом
$APPLICATION->ShowAjaxHead();

$APPLICATION->IncludeComponent(
    'otus:cars.grid',
    '',
    [
        'CONTACT_ID' => $_REQUEST['CONTACT_ID'] ?? 0
    ]
);

// Обязательно закрываем соединение корректно
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');