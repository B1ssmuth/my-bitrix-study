<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use App\Models\Lists\CarsTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Contract\Controllerable;

class CarsGridComponent extends \CBitrixComponent implements Controllerable
{
    /**
     * Обязательный метод для Controllerable
     */
    public function configureActions()
    {
        return [
            'createDeal' => [
                'prefilters' => [] // Разрешаем вызов без строгих фильтров (для внутреннего использования)
            ]
        ];
    }

    /**
     * AJAX-метод создания сделки
     */
    public function createDealAction($carId, $contactId, $carTitle)
    {
        Loader::includeModule('crm');
        
        // ВАЖНО: Впиши сюда ID воронки, которую ты только что создал!
        $categoryId = 1; 
        $carFieldCode = 'UF_CRM_1785836988'; // Твой код поля

        $fields = [
            'TITLE' => 'Заказ-наряд: ' . $carTitle, // Сразу делаем красивое название[cite: 2]
            'CATEGORY_ID' => $categoryId,
            'CONTACT_ID' => $contactId,
            $carFieldCode => $carId,
        ];

        $deal = new \CCrmDeal(false);
        $dealId = $deal->Add($fields, true, ['DISABLE_USER_FIELD_CHECK' => true]);

        if (!$dealId) {
            return ['error' => $deal->LAST_ERROR];
        }

        return ['dealId' => $dealId];
    }

    public function executeComponent()
    {
        Loader::includeModule('crm');
        
        $contactId = (int)($this->arParams['CONTACT_ID'] ?? 0);
        $this->arResult['GRID_ID'] = 'cars_grid_' . $contactId;

        $gridOptions = new \Bitrix\Main\Grid\Options($this->arResult['GRID_ID']);
        $sort = $gridOptions->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);

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
            $order = $sort['sort'];

            $cars = CarsTable::getList([
                'filter' => ['=CONTACT_ID' => $contactId],
                'select' => ['ID', 'BRAND', 'MODEL', 'REG_NUMBER', 'YEAR', 'COLOR', 'MILEAGE'],
                'order' => $order
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
                            // Передаем название машины для формирования заголовка сделки
                            'onclick' => 'createDealForCar(' . $car['ID'] . ', ' . $contactId . ', "' . $carTitle . '")' 
                        ]
                    ]
                ];
            }
        }

        $this->includeComponentTemplate();
    }
}