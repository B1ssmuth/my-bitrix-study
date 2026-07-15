<?php
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['\\App\\Properties\\BookingProperty', 'GetUserTypeDescription']
);

$eventManager->addEventHandler(
    'main',
    'onPageStart',
    function () {
        $request = Application::getInstance()->getContext()->getRequest();
        if (!$request->isAdminSection()) {
            $jsPath = '/local/js/timeman_modifier.js';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $jsPath;
            $version = file_exists($fullPath) ? filemtime($fullPath) : time();
            
            Asset::getInstance()->addString(
                '<script src="' . $jsPath . '?v=' . $version . '"></script>',
                false,
                AssetLocation::AFTER_CSS
            );
        }
    }
);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ['\\App\\Events\\SyncDealIblock', 'syncFromIblock']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['\\App\\Events\\SyncDealIblock', 'syncFromIblock']);
$eventManager->addEventHandler('crm', 'OnAfterCrmDealUpdate', ['\\App\\Events\\SyncDealIblock', 'syncFromDeal']);

// Регистрация кастомных REST-методов для ДЗ 12
$eventManager->addEventHandler(
    'rest',
    'OnRestServiceBuildDescription',
    ['\\App\\Rest\\VisitRest', 'OnRestServiceBuildDescription']
);