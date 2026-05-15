<?php
namespace Otus\CrmTab;

class Events
{
    public static function addVisitLogTab(\Bitrix\Main\Event $event)
    {
        $tabs = $event->getParameter('tabs');
        $entityID = $event->getParameter('entityID');

        $tabs[] = [
            'id' => 'tab_visit_log',
            'name' => 'Журнал посещений',
            'loader' => [
                'serviceUrl' => '/local/components/otus/visit.grid/lazyload.php',
                'componentData' => [
                    'ENTITY_ID' => $entityID
                ]
            ]
        ];

        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, ['tabs' => $tabs], 'crm');
    }
}