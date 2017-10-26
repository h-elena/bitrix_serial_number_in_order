<?php

use \Bitrix\Sale\Internals\Entity,
    \Bitrix\Sale\Notify,
    \Bitrix\Sale\Order,
    \Bitrix\Sale\Result;

class Serialnumbers
{
    const EVENT_NAME = 'SERIALNUMBERS_SALE_SERIAL';
    const EVENT_MESSAGE_ID = 'SERIALNUMBERS_SALE_SERIAL';

    private static $sentEventList = array();
    private static $cacheUserData = array();

    public static function err_mess()
    {
        $module_id = "serialnumbers";
        @include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
        return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: Serialnumbers<br>File: ".__FILE__;
    }

    /**
     * Get all data from base
     *
     * @param array $arFilter
     * @return mixed
     */
    public function GetList($arFilter = array())
    {
        $err_mess = (self::err_mess())."<br>Function: GetList<br>Line: ";
        global $DB, $strError;
        $sql = "";
        if(!empty($arFilter['ID'])){
            $sql .= " AND id in (".implode(',', (int)$arFilter['ID']).")";
        }
        if(!empty($arFilter['ACTIVE'])){
            $sql .= " AND ACTIVE='".($arFilter['ACTIVE'] == 'Y' ? 'Y' : 'N')."'";
        }
        $strSql = "SELECT * FROM `b_serialnumbers` WHERE 1=1".$sql;
        $res = $DB->Query($strSql, false, $err_mess.__LINE__);
        return $res;
    }

    /**
     * Delete element from base
     *
     * @param $ID
     * @return bool
     */
    public static function Delete($ID)
    {
        global $DB, $strError;
        $err_mess = (self::err_mess()) . "<br>Function: Delete<br>Line: ";
        $ID = intval($ID);
        $DB->Query("DELETE FROM `b_serialnumbers` WHERE id=$ID", false, $err_mess . __LINE__);
        return true;
    }

    public static function __CheckSite($site)
    {
        if ($site !== false) {
            if (strlen($site) > 0) {
                $res = CSite::GetByID($site);
                if ($arSite = $res->Fetch())
                    $site = $arSite['ID'];
                else
                    $site = false;
            } else
                $site = false;
        }
        return $site;
    }

    /**
     * @param $str
     * @return bool
     */
    public static function CheckFileName($str)
    {
        $io = CBXVirtualIo::GetInstance();
        if (!$io->ValidateFilenameString($str))
            return GetMessage("FILEMAN_NAME_ERR");
        return true;
    }

    /**
     * Add element in base
     *
     * @param $str
     * @return bool
     */
    public function Add($str){
        $err_mess = (self::err_mess())."<br>Function: GetList<br>Line: ";
        $res = false;
        global $DB, $strError, $USER;
        $strSql = "SELECT * FROM `b_serialnumbers` WHERE `number`='".$DB->ForSql($str, 255)."'";
        $res = $DB->Query($strSql, false, $err_mess.__LINE__);
        if(isset($res->result->num_rows) && $res->result->num_rows == 0){
            $DB->PrepareFields("b_serialnumbers");
            $arFields = array(
                "number" => "'".$DB->ForSql($str, 255)."'",
                "active" => "'Y'",
                "date_create" => "'".date('Y-m-d h:i:s')."'",
                "date_update" => "'".date('Y-m-d h:i:s')."'",
                "user_id" => $USER->GetID()
            );
            $DB->StartTransaction();
            $ID = $DB->Insert("b_serialnumbers", $arFields, $err_mess.__LINE__);
            $ID = intval($ID);
            if($ID > 0){
                return true;
            }
        }
        return false;
    }

    /**
     * Update element in base
     *
     * @param $fields
     * @return bool
     */
    public function Update($fields){
        $err_mess = (self::err_mess())."<br>Function: GetList<br>Line: ";
        $res = false;
        global $DB, $strError, $USER;
        $strSql = "SELECT * FROM `b_serialnumbers` WHERE `number`='".$DB->ForSql($fields['number'], 255)."'";
        $res = $DB->Query($strSql, false, $err_mess.__LINE__);
        if(isset($res->result->num_rows) && $res->result->num_rows > 0){
            $DB->PrepareFields("b_serialnumbers");
            $arFields = array(
                "number" => "'".$DB->ForSql($fields['number'], 255)."'",
                "active" => "'".$fields['active']."'",
                "date_create" => "'".$fields['date_create']."'",
                "date_update" => "'".date('Y-m-d h:i:s')."'",
                "user_id" => $USER->GetID()
            );
            $DB->StartTransaction();
            $ID = $DB->Update("b_serialnumbers", $arFields, "WHERE ID='".$fields['id']."'", $err_mess.__LINE__);
            $ID = intval($ID);
            if($ID > 0){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $code
     * @param $event
     *
     * @return bool
     */
    private static function addSentEvent($code, $event)
    {
        if (!self::hasSentEvent($code, $event)) {
            self::$sentEventList[$code][] = $event;
            return true;
        }

        return false;
    }

    /**
     * @param $code
     * @param $event
     *
     * @return bool
     */
    private static function hasSentEvent($code, $event)
    {
        if (!array_key_exists($code, self::$sentEventList))
        {
            return false;
        }

        if (in_array($event, self::$sentEventList[$code]))
        {
            return true;
        }

        return false;
    }

    /**
     * @param Order $order
     *
     * @return null|string
     * @throws \Bitrix\Main\ArgumentException
     */
    protected static function getUserEmail(Order $order)
    {
        $userEmail = "";

        if (!empty(static::$cacheUserData[$order->getUserId()])) {
            $userData = static::$cacheUserData[$order->getUserId()];
            if (!empty($userData['EMAIL'])) {
                $userEmail = $userData['EMAIL'];
            }
        }


        if (empty($userEmail)) {
            /** @var PropertyValueCollection $propertyCollection */
            if ($propertyCollection = $order->getPropertyCollection()) {
                if ($propUserEmail = $propertyCollection->getUserEmail()) {
                    $userEmail = $propUserEmail->getValue();
                    static::$cacheUserData[$order->getUserId()]['EMAIL'] = $userEmail;
                }
            }
        }

        if (empty($userEmail)) {
            $userRes = Bitrix\Main\UserTable::getList(array(
                'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'),
                'filter' => array('=ID' => $order->getUserId()),
            ));
            if ($userData = $userRes->fetch()) {
                static::$cacheUserData[$order->getUserId()] = $userData;
                $userEmail = $userData['EMAIL'];
            }
        }

        return $userEmail;
    }

    /**
     * @param Order $order
     *
     * @return mixed
     */
    public static function getOrderLanguageId(Order $order)
    {
        $siteData = \Bitrix\Main\SiteTable::GetById($order->getSiteId())->fetch();
        return $siteData['LANGUAGE_ID'];
    }

    /**
     * @param \Bitrix\Sale\Internals\Entity $entity
     *
     * @return Result
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function OnSaleOrderPaidSerial(Entity $entity)
    {
        $result = new Result();

        if (Notify::isNotifyDisabled()) {
            return $result;
        }

        if (!$entity instanceof Order) {
            throw new \Bitrix\Main\ArgumentTypeException('entity', '\Bitrix\Sale\Order');
        }

        if (self::hasSentEvent($entity->getId(), self::EVENT_NAME)) {
            return $result;
        }

        if (!$entity->isPaid()) {
            return $result;
        }

        $res = self::GetList(['ACTIVE' => 'Y']);
        if(isset($res->result->num_rows) && $res->result->num_rows > 0){
            $number = $res->GetNext();
        }

        $fields = Array(
            "ORDER_ID" => $entity->getField("ACCOUNT_NUMBER"),
            "ORDER_REAL_ID" => $entity->getField("ID"),
            "ORDER_ACCOUNT_NUMBER_ENCODE" => urlencode(urlencode($entity->getField("ACCOUNT_NUMBER"))),
            "ORDER_DATE" => $entity->getDateInsert()->toString(),
            "EMAIL" => self::getUserEmail($entity),
            "SALE_EMAIL" => \Bitrix\Main\Config\Option::get("sale", "order_email", "order@" . $_SERVER["SERVER_NAME"]),
            "SERIALNUMBERS_SALE_SERIAL" => $number['number']
        );
        AddMessage2Log($fields, __METHOD__);

        $eventName = self::EVENT_NAME;
        $send = true;

        foreach (GetModuleEvents("sale", self::EVENT_MESSAGE_ID, true) as $oldEvent) {
            if (ExecuteModuleEventEx($oldEvent, array($entity->getId(), &$eventName, &$fields)) === false) {
                $send = false;
            }
        }

        if ($send) {
            $event = new \CEvent;
            $event->Send($eventName, $entity->getField('LID'), $fields, "Y", "", array(), self::getOrderLanguageId($entity));
        }

        self::addSentEvent($entity->getId(), self::EVENT_MESSAGE_ID);

        $number['active'] = 'N';
        self::Update($number);

        return $result;
    }
}