<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use App\Models\VisitLogTable;

class VisitGridComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!\Bitrix\Main\Loader::includeModule('otus.crmtab')) {
        return;
        }
        
        $this->arResult['GRID_ID'] = 'visit_log_list';
        $this->arResult['COLUMNS'] = [
            ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
            ['id' => 'PATIENT_NAME', 'name' => 'Пациент', 'default' => true],
            ['id' => 'VISIT_PRICE', 'name' => 'Цена', 'default' => true],
        ];

        $dbRes = VisitLogTable::getList([
            'select' => ['ID', 'PATIENT_NAME', 'VISIT_PRICE'],
            'limit' => 10
        ]);

        while ($res = $dbRes->fetch()) {
            $this->arResult['ROWS'][] = [
                'data' => $res,
                'columns' => $res
            ];
        }

        $this->includeComponentTemplate();
    }
}