<? require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/serialnumbers/include.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/serialnumbers/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/serialnumbers/lib/excel_reader2.php");

// подключим языковой файл
IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("serialnumbers");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("SERIALNUMBERS_ACCESS_DENIED"));
}

$arErrors = array();
$arMessages = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ABS_FILE_NAME = false;
    $WORK_DIR_NAME = false;
    if (isset($_REQUEST["URL_DATA_FILE"]) && (strlen($_REQUEST["URL_DATA_FILE"]) > 0)) {
        $filename = trim(str_replace("\\", "/", trim($_REQUEST["URL_DATA_FILE"])), "/");
        $FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/" . $filename);
        $info = new SplFileInfo($filename);
        $typeFile = $info->getExtension();
        if($typeFile == 'xls') {
            if ((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/" . $filename) && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W")) {
                $io = CBXVirtualIo::GetInstance();
                $site = Serialnumbers::__CheckSite($site);
                $DOC_ROOT = CSite::GetSiteDocRoot($site);
                $path = $io->CombinePath("/", $path);
                $bQuota = true;
                $pathto = Rel2Abs($path, $filename);
                if(file_exists($DOC_ROOT . $pathto)){
                    unlink($DOC_ROOT . $pathto);
                }
                if (!$USER->CanDoFileOperation('fm_upload_file', Array($site, $pathto))) {
                    $arErrors[] = GetMessage("SERIALNUMBERS_FILEUPLOAD_ACCESS_DENIED") . " \"" . $pathto . "\"\n";
                }
                elseif ($_FILES['file']["error"] == 1 || $_FILES['file']["error"] == 2) {
                    $arErrors[] = GetMessage("SERIALNUMBERS_FILEUPLOAD_SIZE_ERROR", Array('#FILE_NAME#' => $pathto)) . "\n";
                }
                elseif (($mess = Serialnumbers::CheckFileName(str_replace('/', '', $pathto))) !== true) {
                    $arErrors[] = $mess . ".\n";
                }
                else if ($io->FileExists($DOC_ROOT . $pathto)) {
                    $arErrors[] = GetMessage("SERIALNUMBERS_FILEUPLOAD_FILE_EXISTS1") . " \"" . $pathto . "\" " . GetMessage("SERIALNUMBERS_FILEUPLOAD_FILE_EXISTS2") . ".\n";
                }
                elseif (!$USER->IsAdmin() && (HasScriptExtension($pathto) || substr($filename, 0, 1) == ".")) {
                    $arErrors[] = GetMessage("FSERIALNUMBERS_FILEUPLOAD_PHPERROR") . " \"" . $pathto . "\".\n";
                }
                else {
                    $bQuota = true;
                    if (COption::GetOptionInt("main", "disk_space") > 0) {
                        $f = $io->GetFile($_FILES['file']["tmp_name"]);
                        $bQuota = false;
                        $size = $f->GetFileSize();
                        $quota = new CDiskQuota();
                        if ($quota->checkDiskQuota(array("FILE_SIZE" => $size)))
                            $bQuota = true;
                    }

                    if ($bQuota) {
                        if (!$s = $io->Copy($_FILES['file']["tmp_name"], $DOC_ROOT . $pathto)) {
                            $arErrors[] = GetMessage("SERIALNUMBERS_FILEUPLOAD_FILE_CREATE_ERROR") . " \"" . $pathto . "\"\n";
                        }
                        elseif (COption::GetOptionInt("main", "disk_space") > 0) {
                            CDiskQuota::updateDiskQuota("file", $size, "copy");
                        }
                        $f = $io->GetFile($DOC_ROOT . $pathto);
                        $f->MarkWritable();
                        $module_id = 'serialnumbers';
                        if (COption::GetOptionString($module_id, "log_page", "Y") == "Y") {
                            $res_log['path'] = substr($pathto, 1);
                            CEventLog::Log(
                                "INFO",
                                "FILE_ADD",
                                $module_id,
                                __METHOD__,
                                serialize($res_log)
                            );
                        }
                    }
                    else {
                        $arErrors[] = $quota->LAST_ERROR . "\n";
                    }
                }
            }
        }
        else{
            $arErrors[] = GetMessage("SERIALNUMBERS_ERROR_FILE_TYPE");
        }
    }

    if(empty($arErrors)) {
        if (!check_bitrix_sessid()) {
            $arErrors[] = GetMessage("SERIALNUMBERS_ACCESS_DENIED");
        }
        elseif (file_exists($DOC_ROOT . $pathto)) {
            $obj = new Serialnumbers();
            $obXLSFile = new Spreadsheet_Excel_Reader();
            $obXLSFile->setOutputEncoding('CP1251');
            $obXLSFile->setUTFEncoder('mb');
            $obXLSFile->read($DOC_ROOT . $pathto);
            $rowsCount = $obXLSFile->sheets[0]['numRows'];
            for ($i=0;$i<=$rowsCount;$i++){
                $str = current($obXLSFile->sheets[0]['cells'][$i]);
                $str = mb_convert_encoding($str, 'UTF-8', 'WINDOWS-1251');
                if(!empty($str)){
                    $obj->Add($str);
                }
            }
            $arMessages[] = GetMessage("SERIALNUMBERS_SUCCESS");
        }
        else {
            $arErrors[] = GetMessage("SERIALNUMBERS_FILE_ERROR");
        }
    }
    unlink($DOC_ROOT . $pathto);
}

$aMenu = array(
    array(
        "TEXT" => GetMessage("SERIALNUMBERS_BACK"),
        "LINK" => "serialnumbers_list.php?lang=".LANG,
        "ICON" => "btn_list",
    )
);

$aContext = $aMenu;
$context = new CAdminContextMenu($aMenu);

$APPLICATION->SetTitle(GetMessage("SERIALNUMBERS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php"); ?>
<? CAdminMessage::ShowMessage($strWarning); ?>
    <div id="tbl_iblock_import_result_div"></div>
<?

$context->Show();
$aTabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => GetMessage("SERIALNUMBERS_TAB"),
        "ICON" => "main_user_edit",
        "TITLE" => GetMessage("SERIALNUMBERS_TAB_TITLE"),
    ),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

foreach ($arErrors as $strError)
    CAdminMessage::ShowMessage($strError);
foreach ($arMessages as $strMessage)
    CAdminMessage::ShowMessage(array("MESSAGE" => $strMessage, "TYPE" => "OK"));
?>
    <script language="JavaScript" type="text/javascript">
        function NewFileName(ob) {
            var
                filename,
                str_file = ob.value;

            str_file = str_file.replace(/\\/g, '/');
            filename = str_file.substr(str_file.lastIndexOf("/") + 1);
            document.getElementById('URL_DATA_FILE').value = filename;
        }
    </script>
    <form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>?lang=<? echo htmlspecialcharsbx(LANG) ?>" name="serialnumbers"
          enctype="multipart/form-data">
        <?=bitrix_sessid_post()?>
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <tr class="adm-detail-required-field">
            <td width="40%"><? echo GetMessage("SERIALNUMBERS_URL_DATA_FILE") ?>:</td>
            <td width="60%">
                <input type="text" id="URL_DATA_FILE" name="URL_DATA_FILE" size="30" autocomplete="off"
                       value="<?= htmlspecialcharsbx($URL_DATA_FILE) ?>">
                <input type="file" name="file" size="30" maxlength="255" value="" autocomplete="off"
                       onChange="NewFileName(this)">
            </td>
        </tr>
        <? $tabControl->Buttons(); ?>
        <input type="submit" id="start_button" value="<? echo GetMessage("SERIALNUMBERS_START_IMPORT") ?>" class="adm-btn-save">
        <? $tabControl->End(); ?>
    </form>
<?
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>