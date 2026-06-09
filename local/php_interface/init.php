<?
if (file_exists(__DIR__ . '/../../vendor/autoload.php'))
{
    require_once(__DIR__ . '/../../vendor/autoload.php');
}

if (file_exists(__DIR__ . '/../App/autoload.php'))
{
    require_once(__DIR__ . '/../App/autoload.php');
}

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['\\App\\Properties\\BookingProperty', 'GetUserTypeDescription']
);

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'main',
    'onPageStart',
    function () {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        if (!$request->isAdminSection()) {
            
            $jsPath = '/local/js/timeman_modifier.js';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $jsPath;
            $version = file_exists($fullPath) ? filemtime($fullPath) : time();
            
            \Bitrix\Main\Page\Asset::getInstance()->addString(
                '<script src="' . $jsPath . '?v=' . $version . '"></script>',
                false,
                \Bitrix\Main\Page\AssetLocation::AFTER_CSS
            );
        }
    }
);

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['\\App\\Properties\\BookingProperty', 'GetUserTypeDescription']
);