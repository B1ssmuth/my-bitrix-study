<?php

namespace App\Models\Lists;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Класс для работы с таблицей автомобилей клиентов (Гараж).
 */
class CarsTable extends DataManager
{
    /**
     * Возвращает имя таблицы в базе данных.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'app_client_cars';
    }

    /**
     * Возвращает карту полей таблицы.
     *
     * @return array
     */
    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true)
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_ID')),

            // Привязка к контакту (клиенту) в CRM
            (new IntegerField('CONTACT_ID'))
                ->configureRequired(true)
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_CONTACT_ID')),

            // Связь с таблицей контактов CRM для удобных выборок
            (new Reference(
                'CONTACT',
                \Bitrix\Crm\ContactTable::class,
                Join::on('this.CONTACT_ID', 'ref.ID')
            ))->configureJoinType(Join::TYPE_INNER),

            (new StringField('BRAND'))
                ->configureRequired(true)
                ->configureSize(100)
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_BRAND')),

            (new StringField('MODEL'))
                ->configureRequired(true)
                ->configureSize(100)
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_MODEL')),

            (new StringField('REG_NUMBER'))
                ->configureRequired(true)
                ->configureSize(20)
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_REG_NUMBER')),

            (new IntegerField('YEAR'))
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_YEAR')),

            (new StringField('COLOR'))
                ->configureSize(50)
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_COLOR')),

            (new IntegerField('MILEAGE'))
                ->configureTitle(Loc::getMessage('APP_CARS_FIELD_MILEAGE')),
        ];
    }
}