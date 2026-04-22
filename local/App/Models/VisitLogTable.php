<?php
namespace App\Models;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Iblock\ElementTable;

class VisitLogTable extends DataManager
{
    public static function getTableName() { return 'b_visit_log'; }

    public static function getMap()
    {
        return [
            'ID' => new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            'DOCTOR_ID' => new IntegerField('DOCTOR_ID'),
            'PROCEDURE_ID' => new IntegerField('PROCEDURE_ID'),
            'PATIENT_NAME' => new StringField('PATIENT_NAME'),
            'VISIT_PRICE' => new FloatField('VISIT_PRICE'),

            'DOCTOR' => new Reference(
                'DOCTOR',
                ElementTable::class,
                Join::on('this.DOCTOR_ID', 'ref.ID')
            ),

            'PROCEDURE' => new Reference(
                'PROCEDURE',
                ElementTable::class,
                Join::on('this.PROCEDURE_ID', 'ref.ID')
            ),
        ];
    }
}