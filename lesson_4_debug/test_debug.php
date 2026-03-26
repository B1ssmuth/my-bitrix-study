<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Тестирование дебага");

print_r('<pre>');
print_r('$_SERVER: ');
print_r($_SERVER);
print_r('</pre>');

// print_r('<pre>');
// print_r('var_dump($_SERVER): ');
// print_r(var_dump($_SERVER));
// print_r('</pre>');


// Bitrix\Main\Diag\Debug::dump($_SERVER, 'SERVER');
// Bitrix\Main\Diag\Debug::writeToFile($_SERVER, 'SERVER', 'local/logs/server.log');
// Bitrix\Main\Diag\Debug::dumpToFile($_SERVER, 'SERVER');


// Bitrix\Main\Diag\Debug::startTimeLabel('SERVER1');
// sleep(2);
// Bitrix\Main\Diag\Debug::endTimeLabel('SERVER1');
// Bitrix\Main\Diag\Debug::startTimeLabel('SERVER');
// sleep(3);
// Bitrix\Main\Diag\Debug::endTimeLabel('SERVER');
// $TimeLabels = Bitrix\Main\Diag\Debug::getTimeLabels();
// print_r('<pre>');
// print_r('$TimeLabels: ');
// print_r($TimeLabels);
// print_r('</pre>');

// \App\Debug\Log::addLog('TEST');


// $h = 1/0;

// dump($_SERVER);



//region Отладка SQL
/*
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

if (!Loader::includeModule('iblock'))
{
    return false;
}

$IBLOCK_ID = 7; // Фотогалерея пользователей
// bitrix/admin/iblock_list_admin.php?IBLOCK_ID=7&type=photos&lang=ru&find_section_section=-1

// Параметры выборки
$arEntityDataParams = [
    'select' => ['ID', 'NAME'],
    'filter' => ['IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y'],
    'limit'  => 5
];

// Включаем трекинг SQL
$connection = Application::getConnection();
$connection->startTracker();

// Выполняем запрос
$query = \Bitrix\Iblock\ElementTable::getList($arEntityDataParams);
$result = $query->fetchAll(); // Можно или fetch() в цикле

// Отключаем трекер
$connection->stopTracker();

// Получаем SQL-запрос
$sql = $query->getTrackerQuery()->getSql();

// Вывод
echo "<pre>";
print_r($result);
echo "SQL запрос:\n" . $sql;
echo "</pre>";

//*/
//endregion Отладка SQL




require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

