<?php
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $arResult['GRID_ID'],
    'COLUMNS' => $arResult['COLUMNS'],
    'ROWS' => $arResult['ROWS'],
    'SHOW_ROW_CHECKBOXES' => false,
    'AJAX_MODE' => 'Y',
]);