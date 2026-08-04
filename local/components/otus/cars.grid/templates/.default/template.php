<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
// Принудительно грузим скрипты грида и кнопок
\Bitrix\Main\UI\Extension::load(["ui.buttons", "ui.grid", "main.ui.grid"]);
?>

<div class="cars-grid-container" style="padding: 15px;">
    <?php
    $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['COLUMNS'],
        'ROWS' => $arResult['ROWS'],
        'SHOW_ROW_CHECKBOXES' => false,
        'NAV_OBJECT' => false,
        'AJAX_MODE' => 'N',
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' => [
            ['NAME' => "5", 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20']
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => false,
        'SHOW_PAGINATION'           => false,
        'SHOW_SELECTED_COUNTER'     => false,
        'SHOW_TOTAL_COUNTER'        => false,
        'SHOW_PAGESIZE'             => false,
        'SHOW_ACTION_PANEL'         => false,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
    ]);
    ?>
</div>

<script>
    // Заготовка функции для всплывающего окна с историей ремонта
    function showCarHistory(carId, carTitle) {
        BX.SidePanel.Instance.open('/local/components/otus/cars.grid/slider_history.php?car_id=' + carId + '&car_title=' + encodeURIComponent(carTitle), {
            width: 800,
            cacheable: false
        });
    }

    function createDealForCar(carId, contactId) {
        // Открываем слайдер создания сделки и передаем параметры в URL
        BX.SidePanel.Instance.open(
            '/crm/deal/details/0/?contact_id=' + contactId + '&UF_CRM_1785836988=' + carId, 
            { cacheable: false }
        );
    }
</script>