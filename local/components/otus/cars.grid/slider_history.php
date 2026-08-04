<?php
// Отключаем лишнюю статистику
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');

// Подключаем ядро без визуального шаблона (без левого меню и шапки)
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\UI\Extension::load(["ui.buttons", "ui.grid", "main.ui.grid"]);

$carId = (int)($_REQUEST['car_id'] ?? 0);
$carTitle = $_REQUEST['car_title'] ?? 'История обслуживания';

// Имя клиента по ТЗ должно быть в заголовке окна. 
// Мы передадим его чуть позже, когда доработаем js-вызов, пока выводим то, что есть.
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <?php $APPLICATION->ShowHead(); ?>
    <style>
        /* Стилизуем фон слайдера под стандарты Битрикс24 */
        body { background: #eef2f4 !important; padding: 20px; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }
        .history-header { margin-bottom: 20px; font-size: 24px; color: #333; font-weight: normal; }
    </style>
</head>
<body>
    <div class="history-header">
        <?= htmlspecialcharsbx($carTitle) ?>
    </div>

    <div class="history-grid-container" style="background: #fff; padding: 15px; border-radius: 4px;">
        <?php
        // Колонки для истории сделок строго по ТЗ[cite: 2]
        $columns = [
            ['id' => 'TITLE', 'name' => 'Название', 'default' => true],
            ['id' => 'DATE_CREATE', 'name' => 'Дата создания', 'default' => true],
            ['id' => 'STAGE_ID', 'name' => 'Стадия', 'default' => true],
            ['id' => 'ASSIGNED_BY', 'name' => 'Ответственный', 'default' => true],
            ['id' => 'OPPORTUNITY', 'name' => 'Сумма', 'default' => true],
            ['id' => 'SPARE_PARTS', 'name' => 'Список запчастей', 'default' => true],
        ];

        // Временно оставляем массив пустым. 
        // На следующем этапе мы сделаем выборку реальных сделок из CRM по ID автомобиля.
        $rows = [];

        $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
            'GRID_ID' => 'car_history_deals_' . $carId,
            'COLUMNS' => $columns,
            'ROWS' => $rows,
            'SHOW_ROW_CHECKBOXES' => false,
            'SHOW_GRID_SETTINGS_MENU' => false,
            'SHOW_PAGINATION' => false,
            'AJAX_MODE' => 'N',
        ]);
        ?>
    </div>
</body>
</html>
<?php
// Корректно завершаем скрипт
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");