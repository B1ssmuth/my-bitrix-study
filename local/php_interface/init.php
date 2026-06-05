<?
// composer
if (file_exists(__DIR__ . '/../../vendor/autoload.php'))
{
    require_once(__DIR__ . '/../../vendor/autoload.php');
}

// App
if (file_exists(__DIR__ . '/../App/autoload.php'))
{
    require_once(__DIR__ . '/../App/autoload.php');
}

// Регистрация кастомного свойства ИБ
\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['\\App\\Properties\\BookingProperty', 'GetUserTypeDescription']
);

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'main',
    'OnEpilog',
    function () {
        if (!defined('ADMIN_SECTION') && ADMIN_SECTION !== true && $GLOBALS['USER']->IsAuthorized()) {
            \Bitrix\Main\Page\Asset::getInstance()->addJs('/local/js/timeman_modifier.js');
        }
    }
);