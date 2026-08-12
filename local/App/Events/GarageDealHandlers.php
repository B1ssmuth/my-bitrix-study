<?php

namespace App\Events;

use Bitrix\Main\Loader;

class GarageDealHandlers
{
    /**
     * Проверяет наличие незакрытых сделок по автомобилю перед созданием новой
     */
    public static function checkOpenDeals(&$arFields)
    {
        Loader::includeModule('crm');
        
        $carFieldCode = 'UF_CRM_1785836988';

        // Если к сделке привязана машина
        if (!empty($arFields[$carFieldCode])) {
            $carId = (int)$arFields[$carFieldCode];

            // Ищем активные сделки по этой машине
            $dbRes = \CCrmDeal::GetListEx(
                [],
                [
                    '=' . $carFieldCode => $carId,
                    '=CATEGORY_ID' => 1,
                    '!=STAGE_SEMANTIC_ID' => ['S', 'F'],
                    'CHECK_PERMISSIONS' => 'N'
                ],
                false,
                ['nTopCount' => 1],
                ['ID']
            );

            if ($openDeal = $dbRes->Fetch()) {
                // Если нашли активную сделку - блокируем создание новой
                global $APPLICATION;
                $APPLICATION->ThrowException('Ошибка! По данному автомобилю уже есть незакрытый заказ-наряд (Сделка #' . $openDeal['ID'] . '). Закройте предыдущий заказ перед созданием нового.');
                return false;
            }
        }
        
        return true;
    }
}