<?php
// Отключаем лишнюю статистику
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');

// Подключаем ядро без визуального шаблона
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\UI\Extension::load(["ui.buttons", "ui.grid", "main.ui.grid"]);

$carId = (int)($_REQUEST['car_id'] ?? 0);
$carTitle = $_REQUEST['car_title'] ?? 'История обслуживания';

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
        <?= $carTitle // Уже обработано через htmlspecialcharsbx в классе ?>
    </div>

    <div class="history-grid-container" style="background: #fff; padding: 15px; border-radius: 4px;">
        <?php
        // Колонки для истории сделок
        $columns = [
            ['id' => 'TITLE', 'name' => 'Название', 'default' => true],
            ['id' => 'DATE_CREATE', 'name' => 'Дата создания', 'default' => true],
            ['id' => 'STAGE_ID', 'name' => 'Стадия', 'default' => true],
            ['id' => 'ASSIGNED_BY', 'name' => 'Ответственный', 'default' => true],
            ['id' => 'OPPORTUNITY', 'name' => 'Сумма', 'default' => true],
            ['id' => 'SPARE_PARTS', 'name' => 'Список запчастей', 'default' => true],
        ];

        \Bitrix\Main\Loader::includeModule('crm');

        $carFieldCode = 'UF_CRM_1785836988'; 

        $rows = [];
        
        if ($carId > 0) {
            // Запрашиваем сделки, привязанные к этому автомобилю
            $dealRes = \CCrmDeal::GetListEx(
                ['DATE_CREATE' => 'DESC'],
                [$carFieldCode => $carId, 'CHECK_PERMISSIONS' => 'N'], // Обходим права для системного показа
                false,
                false,
                ['ID', 'TITLE', 'DATE_CREATE', 'STAGE_ID', 'ASSIGNED_BY_ID', 'ASSIGNED_BY_NAME', 'ASSIGNED_BY_LAST_NAME', 'OPPORTUNITY', 'CURRENCY_ID']
            );

            // Получаем человекопонятные названия стадий для Воронки 1
            $stages = \CCrmStatus::GetStatusList('DEAL_STAGE_1');

            while ($deal = $dealRes->Fetch()) {
                
                // Формируем ссылку на профиль ответственного
                $userLink = '<a href="/company/personal/user/'.$deal['ASSIGNED_BY_ID'].'/" target="_blank">' . 
                            htmlspecialcharsbx($deal['ASSIGNED_BY_NAME'] . ' ' . $deal['ASSIGNED_BY_LAST_NAME']) . 
                            '</a>';

                // Получаем товары сделки (список запчастей)
                $products = \CCrmDeal::LoadProductRows($deal['ID']);
                $partsList = [];
                foreach ($products as $product) {
                    $partsList[] = htmlspecialcharsbx($product['PRODUCT_NAME']) . ' (' . (float)$product['QUANTITY'] . ' шт.)';
                }
                $partsString = empty($partsList) ? '-' : implode('<br>', $partsList);

                // Форматируем сумму
                $currency = $deal['CURRENCY_ID'] ?: \CCrmCurrency::GetBaseCurrencyID();
                $money = \CCrmCurrency::MoneyToString($deal['OPPORTUNITY'], $currency);

                $rows[] = [
                    'id' => $deal['ID'],
                    'data' => [
                        'TITLE' => htmlspecialcharsbx($deal['TITLE']),
                        'DATE_CREATE' => FormatDate('d.m.Y H:i', MakeTimeStamp($deal['DATE_CREATE'])),
                        'STAGE_ID' => htmlspecialcharsbx($stages[$deal['STAGE_ID']] ?? $deal['STAGE_ID']),
                        'ASSIGNED_BY' => $userLink,
                        'OPPORTUNITY' => $money,
                        'SPARE_PARTS' => $partsString,
                    ]
                ];
            }
        }

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