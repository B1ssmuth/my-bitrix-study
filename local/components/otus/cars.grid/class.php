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

        // Уникальный ID грида
        $this->arResult['GRID_ID'] = 'cars_grid_' . $contactId;

        // Инициализируем настройки грида для работы "шестеренки"
        $gridOptions = new \Bitrix\Main\Grid\Options($this->arResult['GRID_ID']);
        $sort = $gridOptions->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);

        // Колонки
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
            // Чтобы сортировка работала при клике на заголовки
            $order = $sort['sort'];

            $cars = CarsTable::getList([
                'filter' => ['=CONTACT_ID' => $contactId],
                'select' => ['ID', 'BRAND', 'MODEL', 'REG_NUMBER', 'YEAR', 'COLOR', 'MILEAGE'],
                'order' => $order // Применяем сортировку
            ])->fetchAll();

            foreach ($cars as $car) {
                $carTitle = htmlspecialcharsbx($car['BRAND'] . ' ' . $car['MODEL'] . ' - ' . $car['REG_NUMBER']);
                
                $this->arResult['ROWS'][] = [
                    'data' => $car,
                    'actions' => [
                        [
                            'text' => 'История обслуживания',
                            'onclick' => 'showCarHistory(' . $car['ID'] . ', "' . $carTitle . '")'
                        ],
                        [
                            'text' => 'Создать заказ-наряд',
                            // Передаем ID машины и ID контакта
                            'onclick' => 'createDealForCar(' . $car['ID'] . ', ' . $contactId . ')' 
                        ]
                    ]
                ];
            }
        }

        $this->includeComponentTemplate();
    }
}