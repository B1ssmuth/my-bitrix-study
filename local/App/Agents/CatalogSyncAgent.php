<?php

namespace App\Agents;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Catalog\Model\Product;

class CatalogSyncAgent
{
    /**
     * Агент ежедневной синхронизации остатков запчастей.
     * 
     * @return string
     */
    public static function syncQuantities(): string
    {
        Loader::includeModule('crm');
        Loader::includeModule('catalog');
        Loader::includeModule('im'); // Для отправки уведомлений

        $httpClient = new HttpClient();
        
        // В ТЗ указан конкретный URL для получения случайного числа
        $url = 'https://www.random.org/integers/?num=1&min=0&max=10&col=1&base=10&format=plain&rnd=new';

        // Получаем все товары из стандартного каталога CRM
        $rsProducts = \CCrmProduct::GetList([], ['ACTIVE' => 'Y'], ['ID', 'NAME']);
        
        while ($product = $rsProducts->Fetch()) {
            $productId = (int)$product['ID'];
            
            $response = $httpClient->get($url);
            $quantity = (int)trim($response);

            // По ТЗ: закупаем только если реальный остаток 0[cite: 2]
            if ($quantity === 0) {
                $quantity = 10; // Устанавливаем в 10 единиц[cite: 2]
                
                self::notifyPurchaser($product['NAME']);
                self::createAutoPurchaseRequest($product['NAME'], 10);
            }

            // Надежный метод обновления остатка
            \CCatalogProduct::Update($productId, ['QUANTITY' => $quantity]);
        }

        // Возвращаем строку вызова для того, чтобы агент запустился снова на следующий день
        return "\\App\\Agents\\CatalogSyncAgent::syncQuantities();";
    }

    /**
     * Отправляет уведомление закупщику.
     */
    private static function notifyPurchaser(string $productName): void
    {
        // Временно укажем ID администратора (твой). 
        // Позже при настройке прав заменим на поиск пользователей с ролью "Закупщик"[cite: 2].
        $purchaserUserId = 1; 

        $message = "Запчасть «{$productName}» кончилась. Автоматически создана заявка и закуплено 10 штук.";

        \CIMNotify::Add([
            'TO_USER_ID' => $purchaserUserId,
            'FROM_USER_ID' => 0,
            'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
            'NOTIFY_MODULE' => 'crm',
            'NOTIFY_MESSAGE' => $message
        ]);
    }

    /**
     * Создает запись о закупке в Универсальном списке.
     */
    private static function createAutoPurchaseRequest(string $productName, int $quantity): void
    {
        Loader::includeModule('iblock');

        $iblockId = 24; // ID списка закупок

        $el = new \CIBlockElement;
        
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => 'Автозакупка: ' . $productName,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                88 => $productName, // Запчасть
                89 => $quantity,    // Количество
            ]
        ];

        $el->Add($fields);
    }
}