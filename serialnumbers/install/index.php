<?

Class serialnumbers extends CModule
{
    var $MODULE_ID = "serialnumbers";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function serialnumbers(){
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = GetMessage("SERIALNUMBERS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("SERIALNUMBERS_MODULE_DESCRIPTION");
    }

    function InstallDB(){
        global $APPLICATION, $DB, $errors;

        if (!$DB->Query("SELECT 'id' FROM b_serialnumbers", true)) $EMPTY = "Y"; else $EMPTY = "N";

        $errors = false;

        if ($EMPTY=="Y")
        {
            $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serialnumbers/install/db/".strtolower($DB->type)."/install.sql");
        }

        if (!empty($errors))
        {
            $APPLICATION->ThrowException(implode("", $errors));
            return false;
        }

        RegisterModule("serialnumbers");
        RegisterModuleDependences("sale", "OnSaleOrderPaid", "serialnumbers", "Serialnumbers", "OnSaleOrderPaidSerial");

        return true;
    }

    function InstallFiles(){
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serialnumbers/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
        return true;
    }

    function InstallEvents() {
        global $DB;
        include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serialnumbers/install/events.php");
        return true;
    }

    function DoInstall(){
        global $DOCUMENT_ROOT, $APPLICATION;
        $errors = false;
        $this->InstallFiles();
        if($this->InstallDB()) {
            $this->InstallEvents();
        }
        $GLOBALS["errors"] = $this->errors;
        $APPLICATION->IncludeAdminFile(GetMessage("SERIALNUMBERS_INSTALL_TEXT"), $DOCUMENT_ROOT."/bitrix/modules/serialnumbers/install/step.php");
    }

    function UnInstallDB()
    {
        global $APPLICATION, $DB, $errors;

        $errors = false;
        $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serialnumbers/install/db/".strtolower($DB->type)."/uninstall.sql");

        if (!empty($errors))
        {
            $APPLICATION->ThrowException(implode("", $errors));
            return false;
        }

        UnRegisterModuleDependences("sale", "OnSaleOrderPaid", "serialnumbers", "Serialnumbers", "OnSaleOrderPaidSerial");
        COption::RemoveOption("serialnumbers");
        UnRegisterModule("serialnumbers");

        return true;
    }

    function UnInstallEvents(){
        global $DB;
        $eventType = new CEventType;
        $eventM = new CEventMessage;
        $eventType->Delete("SERIALNUMBERS_SALE_SERIAL");
        $dbEvent = CEventMessage::GetList($b = "ID", $order = "ASC", Array("EVENT_NAME" => "SERIALNUMBERS_SALE_SERIAL"));
        while ($arEvent = $dbEvent->Fetch()) {
            $eventM->Delete($arEvent["ID"]);
        }

        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serialnumbers/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        $this->UnInstallEvents();
        return true;
    }

    function DoUninstall(){
        global $DOCUMENT_ROOT, $APPLICATION;

        $errors = false;
        $this->UnInstallDB();
        $this->UnInstallFiles();
        $APPLICATION->IncludeAdminFile(GetMessage("SERIALNUMBERS_UNINSTALL_TEXT"), $DOCUMENT_ROOT."/bitrix/modules/serialnumbers/install/unstep.php");
    }
}