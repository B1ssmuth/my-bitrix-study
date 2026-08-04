<?php

namespace Otus\CrmTab;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Events
{
    /**
     * Обработчик добавления кастомной вкладки в карточку CRM
     */
    public static function onEntityDetailsTabsInitialized(Event $event)
    {
        $entityID = $event->getParameter('entityID');
        $entityTypeID = $event->getParameter('entityTypeID');
        $tabs = $event->getParameter('tabs');

        // По ТЗ: вкладка должна быть у контакта-физлица[cite: 2]
        if ($entityTypeID === \CCrmOwnerType::Contact) {
            $tabs[] = [
                'id' => 'garage_tab',
                // Используем языковую фразу, если нет - fallback на "Гараж"[cite: 2]
                'name' => Loc::getMessage('OTUS_CRMTAB_GARAGE_NAME') ?: 'Гараж',
                'loader' => [
                    // Ссылка на lazyload файл нашего будущего компонента
                    'serviceUrl' => '/local/components/otus/cars.grid/lazyload.php?&site=' . SITE_ID . '&' . bitrix_sessid_get(),
                    'componentData' => [
                        'template' => '',
                        'params' => [
                            'CONTACT_ID' => $entityID
                        ]
                    ]
                ]
            ];
            
            return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs], 'otus.crmtab');
        }
    }
}