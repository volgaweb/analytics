<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class vw_analytics extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'vw.analytics';
        $this->MODULE_NAME = Loc::getMessage('VW_ANALYTICS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('VW_ANALYTICS_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('VW_ANALYTICS_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://volga-w.ru';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        RegisterModuleDependences('form', 'onBeforeResultAdd', $this->MODULE_ID, "\\VW\\Analytics\\Webform", 'onBeforeResultAdd', 100500);
        RegisterModuleDependences('form', 'onAfterResultAdd', $this->MODULE_ID, "\\VW\\Analytics\\Webform", 'onAfterResultAdd', 100500);
        RegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, "\\VW\\Analytics\\Handlers", 'OnBeforeEventSend', 100500);
        $this->installDB();
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        UnRegisterModuleDependences('form', 'onBeforeResultAdd', $this->MODULE_ID, "\\VW\\Analytics\\Webform", 'onBeforeResultAdd', 100500);
        UnRegisterModuleDependences('form', 'onAfterResultAdd', $this->MODULE_ID, "\\VW\\Analytics\\Webform", 'onAfterResultAdd', 100500);
        UnRegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, "\\VW\\Analytics\\Handlers", 'OnBeforeEventSend', 100500);
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
//            ExampleTable::getEntity()->createDbTable();
        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
//            $connection = Application::getInstance()->getConnection();
//            $connection->dropTable(ExampleTable::getTableName());
        }
    }
}
