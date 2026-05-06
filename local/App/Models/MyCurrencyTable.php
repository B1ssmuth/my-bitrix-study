<?php
namespace App\Models;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\FloatField;

class MyCurrencyTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_catalog_currency';
    }

    public static function getMap()
    {
        return [
            'CURRENCY' => new StringField('CURRENCY', [
                'primary' => true,
            ]),
            'AMOUNT_CNT' => new FloatField('AMOUNT_CNT'),
            'AMOUNT' => new FloatField('AMOUNT'), 
        ];
    }
}