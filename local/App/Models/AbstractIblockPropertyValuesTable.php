<?php
namespace App\Models;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;

abstract class AbstractIblockPropertyValuesTable extends DataManager {
    public const IBLOCK_ID = null;
    protected static ?array $properties = null;

    public static function getTableName(): string {
        return 'b_iblock_element_prop_s' . static::IBLOCK_ID;
    }

    public static function getMap(): array {
        Loader::includeModule('iblock');
        $map = [
            'IBLOCK_ELEMENT_ID' => new IntegerField('IBLOCK_ELEMENT_ID', ['primary' => true]),
            'ELEMENT' => new ReferenceField('ELEMENT', ElementTable::class, ['=this.IBLOCK_ELEMENT_ID' => 'ref.ID']),
        ];

        foreach (static::getProperties() as $property) {
            if ($property['MULTIPLE'] === 'Y') {
                $map[$property['CODE'] . '_ELEMENT_NAME'] = new ExpressionField(
                    $property['CODE'] . '_ELEMENT_NAME',
                    sprintf('(SELECT GROUP_CONCAT(e.NAME SEPARATOR "\0") FROM b_iblock_element_prop_m%d as m JOIN b_iblock_element as e ON m.VALUE = e.ID WHERE m.IBLOCK_ELEMENT_ID = %%s AND m.IBLOCK_PROPERTY_ID = %d)',
                        static::IBLOCK_ID, $property['ID']),
                    ['IBLOCK_ELEMENT_ID'],
                    ['fetch_data_modification' => [static::class, 'getMultipleFieldValueModifier']]
                );
            } else {
                $map[$property['CODE']] = new StringField("PROPERTY_{$property['ID']}");
            }
        }
        return $map;
    }

    public static function getProperties(): array {
        if (isset(static::$properties[static::IBLOCK_ID])) return static::$properties[static::IBLOCK_ID];
        $dbResult = PropertyTable::getList(['filter' => ['IBLOCK_ID' => static::IBLOCK_ID], 'select' => ['ID', 'CODE', 'MULTIPLE']]);
        return static::$properties[static::IBLOCK_ID] = $dbResult->fetchAll();
    }

    public static function getMultipleFieldValueModifier(): array {
        return [fn ($value) => array_filter(explode("\0", $value))];
    }
}