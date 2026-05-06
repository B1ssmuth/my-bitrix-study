<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use App\Models\MyCurrencyTable;

class OtusCurrencyRate extends CBitrixComponent
{
    public function executeComponent()
    {
        $currencyCode = $this->arParams["SELECTED_CURRENCY"];

        $this->arResult = MyCurrencyTable::getList([
            'select' => ['CURRENCY', 'AMOUNT'],
            'filter' => ['CURRENCY' => $currencyCode]
        ])->fetch();

        $this->includeComponentTemplate();
    }
}