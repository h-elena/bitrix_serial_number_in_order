<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$templateGeneral = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=#SITE_CHARSET#"/>
	<style>
		body
		{
			font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
			font-size: 14px;
			color: #000;
		}
	</style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="850" style="background-color: #d1d1d1; border-radius: 2px; border:1px solid #d1d1d1; margin: 0 auto;" border="1" bordercolor="#d1d1d1">
	<tr>
		<td height="83" width="850" bgcolor="#eaf3f5" style="border: none; padding-top: 23px; padding-right: 17px; padding-bottom: 24px; padding-left: 17px;">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td bgcolor="#ffffff" height="75" style="font-weight: bold; text-align: center; font-size: 26px; color: #0b3961;">#TITLE#</td>
				</tr>
				<tr>
					<td bgcolor="#bad3df" height="11"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="850" bgcolor="#f7f7f7" valign="top" style="border: none; padding-top: 0; padding-right: 44px; padding-bottom: 16px; padding-left: 44px;">
			<p style="margin-top:30px; margin-bottom: 28px; font-weight: bold; font-size: 19px;">#SUB_TITLE#</p>
			<p style="margin-top: 0; margin-bottom: 20px; line-height: 20px;">#TEXT#</p>
		</td>
	</tr>
	<tr>
		<td height="40px" width="850" bgcolor="#f7f7f7" valign="top" style="border: none; padding-top: 0; padding-right: 44px; padding-bottom: 30px; padding-left: 44px;">
			<p style="border-top: 1px solid #d1d1d1; margin-bottom: 5px; margin-top: 0; padding-top: 20px; line-height:21px;">#FOOTER_BR# <a href="http://#SERVER_NAME#" style="color:#2e6eb6;">#FOOTER_SHOP#</a><br />
				E-mail: <a href="mailto:#SALE_EMAIL#" style="color:#2e6eb6;">#SALE_EMAIL#</a>
			</p>
		</td>
	</tr>
</table>
</body>
</html>';

$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", Array("EVENT_NAME" => 'SERIALNUMBERS_SALE_SERIAL'));
if(!($dbEvent->Fetch())) {
	$langs = CLanguage::GetList(($b=""), ($o=""));
	while($lang = $langs->Fetch()) {
		$lid = $lang["LID"];
		IncludeModuleLangFile(__FILE__, $lid);

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SERIALNUMBERS_SALE_SERIAL",
			"NAME" => GetMessage("SERIALNUMBERS_SALE_SERIAL"),
			"DESCRIPTION" => GetMessage("SERIALNUMBERS_SALE_SERIAL_DESC"),
		));

		$arSites = array();
		$sites = CSite::GetList(($b=""), ($o=""), Array("LANGUAGE_ID"=>$lid));
		while ($site = $sites->Fetch())
			$arSites[] = $site["LID"];

		if(count($arSites) > 0) {
			$template = str_replace("#SITE_CHARSET#", $lang["CHARSET"], $templateGeneral);
            $emess = new CEventMessage;
            $message = str_replace(
                array(
                    "#TITLE#",
                    "#SUB_TITLE#",
                    "#TEXT#",
                    "#FOOTER_BR#",
                    "#FOOTER_SHOP#",
                ),
                array(
                    GetMessage("SERIALNUMBERS_SALE_SERIAL_HTML_TITLE"),
                    GetMessage("SERIALNUMBERS_SALE_SERIAL_HTML_SUB_TITLE"),
                    str_replace("\n", "<br />\n", GetMessage("SERIALNUMBERS_SALE_SERIAL_HTML_TEXT")),
                    GetMessage("SMAIL_FOOTER_BR"),
                    GetMessage("SMAIL_FOOTER_SHOP"),
                ),
                $template);

            $emess->Add(array(
                "ACTIVE" => "Y",
                "EVENT_NAME" => "SERIALNUMBERS_SALE_SERIAL",
                "LID" => $arSites,
                "EMAIL_FROM" => "#SALE_EMAIL#",
                "EMAIL_TO" => "#EMAIL#",
                "BCC" => "#BCC#",
                "SUBJECT" => GetMessage("SERIALNUMBERS_SALE_SERIAL_SUBJECT"),
                "MESSAGE" => $message,
                "BODY_TYPE" => "html"
            ));
		}
	}
}
?>