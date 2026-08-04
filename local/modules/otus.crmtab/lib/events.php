<?php

namespace Otus\CrmTab;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Events
{
    /**
     * Обработчик добавления кастомной вкладки в карточку CRM.
     */
    public static function addVisitLogTab(Event $event)
    {
        $entityID = $event->getParameter('entityID');
        $entityTypeID = $event->getParameter('entityTypeID');
        $tabs = $event->getParameter('tabs');

        // По ТЗ: вкладка должна быть у контакта-физлица[cite: 2]
        if ($entityTypeID === \CCrmOwnerType::Contact) {
            
            // КЛЮЧЕВОЙ МОМЕНТ: Загружаем JS-ядро грида в родительскую страницу CRM
            \Bitrix\Main\UI\Extension::load(["ui.grid", "main.ui.grid", "ui.buttons"]);

            $tabs[] = [
                'id' => 'garage_tab',
                'name' => Loc::getMessage('OTUS_CRMTAB_GARAGE_NAME') ?: 'Гараж',
                'loader' => [
                    'serviceUrl' => '/local/components/otus/cars.grid/lazyload.php?CONTACT_ID=' . $entityID . '&site=' . SITE_ID . '&' . bitrix_sessid_get(),
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