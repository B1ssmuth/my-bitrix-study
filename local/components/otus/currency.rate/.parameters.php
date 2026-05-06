<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use App\Models\MyCurrencyTable;

$arCurrencies = [];
$dbRes = MyCurrencyTable::getList(['select' => ['CURRENCY']]);
while ($res = $dbRes->fetch()) {
    $arCurrencies[$res['CURRENCY']] = $res['CURRENCY'];
}

$arComponentParameters = [
    "PARAMETERS" => [
        "SELECTED_CURRENCY" => [
            "PARENT" => "BASE",
            "NAME" => "Выберите валюту",
            "TYPE" => "LIST",
            "VALUES" => $arCurrencies,
            "DEFAULT" => "USD",
        ],
    ],
];