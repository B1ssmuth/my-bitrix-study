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
                'prefilters' => []
            ],
            'addCar' => [
                'prefilters' => [] // Разрешаем метод добавления авто
            ]
        ];
    }

    /**
     * AJAX-метод создания сделки
     */
    public function createDealAction($carId, $contactId, $carTitle)
    {
        Loader::includeModule('crm');
        
        $categoryId = 1; // Твой ID воронки
        $carFieldCode = 'UF_CRM_1785836988'; // Твой код поля

        // 1. Проверяем наличие открытых сделок до попытки создания для красивого UX
        $dbRes = \CCrmDeal::GetListEx(
            [],
            [
                '=' . $carFieldCode => $carId,
                '=CATEGORY_ID' => $categoryId,
                '!=STAGE_SEMANTIC_ID' => ['S', 'F'], 
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            ['nTopCount' => 1],
            ['ID']
        );

        if ($openDeal = $dbRes->Fetch()) {
            return ['error' => 'Внимание! По данному автомобилю уже есть незакрытый заказ-наряд (Сделка #' . $openDeal['ID'] . '). Закройте его перед созданием нового.'];
        }

        // 2. Если открытых сделок нет - создаем новую
        $fields = [
            'TITLE' => 'Заказ-наряд: ' . $carTitle,
            'CATEGORY_ID' => $categoryId,
            'CONTACT_ID' => $contactId,
            $carFieldCode => $carId,
        ];

        $deal = new \CCrmDeal(false);
        $dealId = $deal->Add($fields, true, ['DISABLE_USER_FIELD_CHECK' => true]);

        if (!$dealId) {
            return ['error' => 'Ошибка создания сделки: ' . $deal->LAST_ERROR];
        }

        return ['dealId' => $dealId];
    }

    /**
     * AJAX-метод добавления нового автомобиля
     */
    public function addCarAction($contactId, $brand, $model, $regNumber, $year, $color, $mileage)
    {
        Loader::includeModule('crm');
        
        $result = CarsTable::add([
            'CONTACT_ID' => (int)$contactId,
            'BRAND' => trim($brand),
            'MODEL' => trim($model),
            'REG_NUMBER' => trim($regNumber),
            'YEAR' => (int)$year,
            'COLOR' => trim($color),
            'MILEAGE' => (int)$mileage
        ]);

        if (!$result->isSuccess()) {
            return ['error' => implode(', ', $result->getErrorMessages())];
        }

        return ['success' => true];
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