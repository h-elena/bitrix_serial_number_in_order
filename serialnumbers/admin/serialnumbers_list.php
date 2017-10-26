<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$sTableID = "tbl_serialnumbers";
$oSort = new CAdminSorting($sTableID, "id", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serialnumbers/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serialnumbers/include.php");

IncludeModuleLangFile(__FILE__);

$SERIALNUMBERS_RIGHT = $APPLICATION->GetGroupRight("serialnumbers");
if ($SERIALNUMBERS_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("SERIALNUMBERS_ACCESS_DENIED"));
}

if (($arID = $lAdmin->GroupAction()) && $SERIALNUMBERS_RIGHT == "W" && check_bitrix_sessid()) {
    if ($_REQUEST['action_target'] == 'selected') {
        $arID = Array();
        $rsData = Serialnumbers::GetList($arFilter);
        while ($arRes = $rsData->Fetch())
            $arID[] = $arRes['ID'];
    }

    foreach ($arID as $ID) {
        if (strlen($ID) <= 0) {
            continue;
        }
        $ID = IntVal($ID);
        switch ($_REQUEST['action']) {
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                if (!Serialnumbers::Delete($ID)) {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
                }
                $DB->Commit();
                break;
        }
    }
}

$rsData = Serialnumbers::GetList();
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SERIALNUMBERS_NAME_MODULE")));

$lAdmin->AddHeaders(array(
    array(  "id"    =>"id",
        "content"  =>"ID",
        "sort"    =>"s_id",
        "align"    =>"right",
        "default"  =>true,
    ),
    array(  "id"    =>"number",
        "content"  =>GetMessage("SERIALNUMBERS_HEADER_NUMBER"),
        "sort"    =>"s_name",
        "default"  =>true,
    ),
    array(  "id"    =>"active",
        "content"  =>GetMessage("SERIALNUMBERS_HEADER_ACTIVE"),
        "sort"    =>"act",
        "default"  =>true,
    )
));

while($arRes = $rsData->NavNext(true, "f_")) {

    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_id, $arRes);

    $row->AddCheckField("active");
}

// list footer
$lAdmin->AddFooter(
    array(
        array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
        array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
    )
);

if ($SERIALNUMBERS_RIGHT=="W") {
    $lAdmin->AddGroupActionTable(Array(
        "delete" => GetMessage("SERIALNUMBERS_DELETE_L"),
    ));
}

if ($SERIALNUMBERS_RIGHT=="W") {
    $aMenu = array();
    $aMenu[] = array(
        "TEXT"	=> GetMessage("SERIALNUMBERS_DOWNLOAD_TEXT"),
        "TITLE"=>GetMessage("SERIALNUMBERS_DOWNLOAD_TEXT"),
        "LINK"=>"serialnumbers_edit.php?lang=".LANG,
        "ICON" => "btn_new"
    );

    $aContext = $aMenu;
    $lAdmin->AddAdminContextMenu($aContext);
}
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SERIALNUMBERS_NAME_MODULE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");