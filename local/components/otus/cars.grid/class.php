<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use App\Models\Lists\CarsTable;
use Bitrix\Main\Localization\Loc;

class CarsGridComponent extends \CBitrixComponent
{
    public function executeComponent()
    {
        Loader::includeModule('crm');
        
        $contactId = (int)($this->arParams['CONTACT_ID'] ?? 0);

        // Уникальный ID грида для конкретного контакта
        $this->arResult['GRID_ID'] = 'cars_grid_' . $contactId;

        // Формируем колонки согласно ТЗ
        $this->arResult['COLUMNS'] = [
            ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false],
            ['id' => 'BRAND', 'name' => Loc::getMessage('APP_CARS_FIELD_BRAND') ?: 'Марка', 'sort' => 'BRAND', 'default' => true],
            ['id' => 'MODEL', 'name' => Loc::getMessage('APP_CARS_FIELD_MODEL') ?: 'Модель', 'sort' => 'MODEL', 'default' => true],
            ['id' => 'REG_NUMBER', 'name' => Loc::getMessage('APP_CARS_FIELD_REG_NUMBER') ?: 'Номер', 'sort' => 'REG_NUMBER', 'default' => true],
            ['id' => 'YEAR', 'name' => Loc::getMessage('APP_CARS_FIELD_YEAR') ?: 'Год', 'sort' => 'YEAR', 'default' => true],
            ['id' => 'COLOR', 'name' => Loc::getMessage('APP_CARS_FIELD_COLOR') ?: 'Цвет', 'sort' => 'COLOR', 'default' => true],
            ['id' => 'MILEAGE', 'name' => Loc::getMessage('APP_CARS_FIELD_MILEAGE') ?: 'Пробег', 'sort' => 'MILEAGE', 'default' => true],
        ];

        $this->arResult['ROWS'] = [];

        if ($contactId > 0) {
            // Запрашиваем автомобили из нашей новой таблицы
            $cars = CarsTable::getList([
                'filter' => ['=CONTACT_ID' => $contactId],
                'select' => ['ID', 'BRAND', 'MODEL', 'REG_NUMBER', 'YEAR', 'COLOR', 'MILEAGE']
            ])->fetchAll();

            foreach ($cars as $car) {
                // Подготавливаем заголовок для будущего всплывающего окна
                $carTitle = htmlspecialcharsbx($car['BRAND'] . ' ' . $car['MODEL'] . ' - ' . $car['REG_NUMBER']);
                
                $this->arResult['ROWS'][] = [
                    'data' => $car,
                    // Добавляем действие при нажатии на строку (вызов всплывающего окна истории)
                    'actions' => [
                        [
                            'text' => 'История обслуживания',
                            'onclick' => 'showCarHistory(' . $car['ID'] . ', "' . $carTitle . '")'
                        ]
                    ]
                ];
            }
        }

        $this->includeComponentTemplate();
    }
}