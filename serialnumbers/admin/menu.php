<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("serialnumbers")>"D") {
    $aMenu = array(
        "parent_menu" => "global_menu_services",
        "sort"        => 100,
        "url"         => "serialnumbers_list.php?lang=".LANGUAGE_ID,
        "text"        => GetMessage("SERIALNUMBERS_MENU_MAIN"),
        "title"       => GetMessage("SERIALNUMBERS_MENU_MAIN_TITLE"),
        "icon"        => "form_menu_icon",
        "page_icon"   => "form_page_icon",
        "items_id"    => "menu_webforms",
        "items"       => array(),
    );
    return $aMenu;
}
return false;
?>