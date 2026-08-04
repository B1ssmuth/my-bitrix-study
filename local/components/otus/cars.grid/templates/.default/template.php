<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
// Принудительно грузим скрипты грида и кнопок
\Bitrix\Main\UI\Extension::load(["ui.buttons", "ui.grid", "main.ui.grid"]);
?>

<div style="margin-bottom: 15px; padding-left: 15px; padding-top: 15px;">
    <button class="ui-btn ui-btn-primary" onclick="openAddCarForm(<?= (int)$arParams['CONTACT_ID'] ?>)">+ Добавить автомобиль</button>
</div>

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

    function createDealForCar(carId, contactId, carTitle) {
        // Отправляем D7-запрос к нашему компоненту
        BX.ajax.runComponentAction('otus:cars.grid', 'createDeal', {
            mode: 'class',
            data: {
                carId: carId,
                contactId: contactId,
                carTitle: carTitle
            }
        }).then(function (response) {
            if (response.data.dealId) {
                // Если сделка создана, открываем её карточку
                BX.SidePanel.Instance.open(
                    '/crm/deal/details/' + response.data.dealId + '/', 
                    { cacheable: false }
                );
            } else if (response.data.error) {
                // Если сработал наш обработчик (например, уже есть открытая сделка), выводим ошибку
                alert(response.data.error);
            }
        }).catch(function(response) {
            // Перехват системных ошибок D7 (включая ThrowException)
            let errorText = 'Произошла ошибка при создании заказ-наряда.';
            if (response.errors && response.errors.length > 0) {
                errorText = response.errors[0].message;
            }
            alert(errorText);
        });
    }

    function openAddCarForm(contactId) {
        BX.SidePanel.Instance.open('/local/components/otus/cars.grid/slider_add_car.php?contact_id=' + contactId, {
            width: 500,
            cacheable: false,
            events: {
                onClose: function() {
                    // Перезагружаем грид с явной передачей CONTACT_ID
                    var gridObject = BX.Main.gridManager.getInstanceById('cars_grid_' + contactId);
                    if (gridObject) {
                        gridObject.reload('/local/components/otus/cars.grid/lazyload.php?CONTACT_ID=' + contactId);
                    }
                }
            }
        });
    }
</script>