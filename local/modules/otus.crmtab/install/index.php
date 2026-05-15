<?php
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

class otus_crmtab extends CModule
{
    var $MODULE_ID = 'otus.crmtab';
    var $MODULE_NAME = 'OTUS: Вкладка Журнал в CRM';
    var $MODULE_VERSION = '1.0.0';
    var $MODULE_VERSION_DATE = '2026-05-15';

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        EventManager::getInstance()->registerEventHandler(
            'crm', 'onEntityDetailsTabsInitialized', $this->MODULE_ID,
            '\Otus\CrmTab\Events', 'addVisitLogTab'
        );
    }

    public function DoUninstall()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'crm', 'onEntityDetailsTabsInitialized', $this->MODULE_ID,
            '\Otus\CrmTab\Events', 'addVisitLogTab'
        );
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}