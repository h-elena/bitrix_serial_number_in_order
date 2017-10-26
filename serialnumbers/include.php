<?
global $DB, $MESS, $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");

IncludeModuleLangFile(__FILE__);

$DBType = strtolower($DB->type);

CModule::AddAutoloadClasses(
	"serialnumbers",
	array(
		// main classes
		"Serialnumbers" => "classes/serialnumbers_class.php"
	)
);
/*
// set event handlers
AddEventHandler('form', 'onAfterResultAdd', array('CFormEventHandlers', 'sendOnAfterResultStatusChange'));
AddEventHandler('form', 'onAfterResultStatusChange', array('CFormEventHandlers', 'sendOnAfterResultStatusChange'));

// append core form field validators
$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/validators";
$handle = opendir($path);
if ($handle)
{
	while(($filename = readdir($handle)) !== false)
	{
		if($filename == "." || $filename == "..")
			continue;

		if (!is_dir($path."/".$filename) && substr($filename, 0, 4) == "val_")
		{
			require_once($path."/".$filename);
		}
	}
}*/
?>