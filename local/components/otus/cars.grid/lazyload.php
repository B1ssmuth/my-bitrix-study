<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$APPLICATION->IncludeComponent(
    'otus:cars.grid',
    '',
    [
        'CONTACT_ID' => $_REQUEST['CONTACT_ID'] ?? 0
    ]
);