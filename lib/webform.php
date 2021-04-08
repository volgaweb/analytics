<?php

namespace VW\Analytics;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use CEventMessage;
use CForm;
use CFormField;
use CFormResult;
use VW\Main\Debug;

class Webform
{
    // массив полей, которые должны быть
    // todo перенести управление в админку
    private static $fields = [
        'ip',
        'yametrika',
        'page'
    ];

    private static $fieldsMailHandled = ['yametrika'];

    /**
     * Устанавливаем отсутствующие поля в вебформу
     * @param $WEB_FORM_ID
     * @param $arFields
     * @param $arrVALUES
     * @throws LoaderException
     */
    public static function onBeforeResultAdd($WEB_FORM_ID, &$arFields, &$arrVALUES)
    {
        static::checkFieldsInForm($WEB_FORM_ID);
    }


    /**
     * Устанавливаем отсутствующие поля в вебформу
     * @param $WEB_FORM_ID
     * @param $arFields
     * @param $arrVALUES
     * @throws LoaderException
     */
    public static function onAfterResultAdd($WEB_FORM_ID, $RESULT_ID)
    {
        static::fillFieldsInForm($RESULT_ID);
    }

    /**
     * Проверить наличие необходимых полей в вебформе, при необходимости добавить
     * @param $WEB_FORM_ID
     * @return bool
     * @throws LoaderException
     */
    public static function checkFieldsInForm($WEB_FORM_ID)
    {
        if (!Loader::includeModule('form')) {
            return false;
        }
        // храним существующие поля
        $currentFields = [];

        // проверяем наличие всех необходимых полей вебформы
        $rsFields = CFormField::GetList(
            $WEB_FORM_ID,
            "Y",
            $by = "s_id",
            $order = "desc",
            [],
            $is_filtered
        );
        while ($arField = $rsFields->Fetch()) {
            $currentFields[] = $arField['SID'];
        }
        // отличия в составе полей
        $diffFields = array_diff(self::$fields, $currentFields);
        $res = [];
        $mailTpl = [];
        foreach ($diffFields as $diffField) {
            $res[] = self::addFieldToWebform($diffField, $WEB_FORM_ID);
            $mailTpl = self::addFieldToMailTemplate($diffField, $WEB_FORM_ID);

        }
        foreach (self::$fields as $field) {

            $mailTpl = self::addFieldToMailTemplate($field, $WEB_FORM_ID);
        }
        return true;
    }

    /**
     * Добавление недостающих полей в вебформу
     * @param $field
     * @param $webformId
     * @return bool|int|string
     */
    private static function addFieldToWebform($field, $webformId)
    {

        $cFormField = new CFormField();

        $arFields = array(
            "SID" => $field,
            "FORM_ID" => $webformId,
            "ACTIVE" => "Y",
            "ADDITIONAL" => "Y",
            "FIELD_TYPE" => "text",
            "TITLE" => Loc::getMessage('VW_ANALYTICS_WEBFORM_' . $field . '_TITLE'),
            "TITLE_TYPE" => 'text',
            "C_SORT" => 1000,
            "FILTER_TITLE" => Loc::getMessage('VW_ANALYTICS_WEBFORM_' . $field . '_FILTER_TITLE'),
            "IN_RESULTS_TABLE" => "Y",
            "IN_EXCEL_TABLE" => "Y",
            "RESULTS_TABLE_TITLE" => Loc::getMessage('VW_ANALYTICS_WEBFORM_' . $field . '_TABLE_TITLE'),
            "arFILTER_FIELD" => array("text")
        );

        // добавим новое поле
        $NEW_ID = $cFormField->Set($arFields, false, "N");

        return $NEW_ID;
    }

    /**
     * Получаем данные для заполнения полей
     * @param $RESULT_ID int
     * @throws LoaderException
     */
    public static function fillFieldsInForm($RESULT_ID)
    {
        foreach (self::$fields as $field) {
            if (method_exists(__NAMESPACE__ . '\\Fields\\' . $field, 'getData')) {
                $data = call_user_func([__NAMESPACE__ . '\\Fields\\' . $field, 'getData']);
            } else {
                $data = null;
            }
            self::fillFormField($RESULT_ID, $field, $data);
        }
    }

    /**
     * Заполняем поля результата вебформы
     * @param $RESULT_ID
     * @param $field
     * @param null $data
     * @return bool
     * @throws LoaderException
     */
    private static function fillFormField($RESULT_ID, $field, $data = null)
    {
        if (!Loader::includeModule('form')) {
            return false;
        }
        return CFormResult::SetField($RESULT_ID, $field, (string)$data);
    }

    /**
     * добавляем новые поля в почтовый шаблон
     * @param $field string
     * @param $WEB_FORM_ID
     * @return bool
     * @throws LoaderException
     */
    private static function addFieldToMailTemplate($field, $WEB_FORM_ID)
    {
        if (!Loader::includeModule('form')) {
            return false;
        }

        // получаем почтовое событие для этой вебформы
        $rsForm = CForm::GetById($WEB_FORM_ID);
        $form = $rsForm->Fetch();
        $mailEventType = $form['MAIL_EVENT_TYPE'];

        //получаем почтовый шаблон для этого события
        $filter = ['TYPE_ID' => $mailEventType];
        $messages = CEventMessage::GetList($by = "site_id", $order = "desc", $filter);

        $messTpls = [];
        while ($arMess = $messages->GetNext()) {
            $messTpls[$arMess['ID']] = ['type' => $arMess['BODY_TYPE'], 'message' => $arMess['~MESSAGE']];
        }
        foreach ($messTpls as $messId => &$messTpl) {
            if (!strpos($messTpl['message'], "#" . $field . "#")) {
                if ($messTpl['type'] == 'text') {
                    $messTpl['message'] .= "
                
" . Loc::getMessage('VW_ANALYTICS_WEBFORM_' . $field . '_TITLE') . '
*******************************
#' . $field . "#";
                } elseif ($messTpl['type'] == 'html') {
                    $messTpl['message'] .= "
                <br/>
                <br/>
" . Loc::getMessage('VW_ANALYTICS_WEBFORM_' . $field . '_TITLE') . '<br/>
*******************************<br/>
#' . $field . "#";
                }

                $arField['MESSAGE'] = $messTpl['message'];

                $em = new CEventMessage;
                $res = $em->Update($messId, $arField);
            }
        }
    }

    public static function handlerFormWebformFields(array &$fields): void
    {
        foreach ($fields as $field => &$value) {
            if (in_array($field, self::$fieldsMailHandled)) {
                if (method_exists(__NAMESPACE__ . '\\Fields\\' . $field, 'handleMailValue')) {
                    $value = call_user_func([__NAMESPACE__ . '\\Fields\\' . $field, 'handleMailValue'], $value);
                }
            }
        }
    }

    public static function getFieldsValue()
    {
        $return = [];
        foreach (self::$fields as $field) {
            if (method_exists(__NAMESPACE__ . '\\Fields\\' . $field, 'getData')) {
                $return[$field] = call_user_func([__NAMESPACE__ . '\\Fields\\' . $field, 'getData']);
            } else {
                $return[$field] = null;
            }
        }
        return $return;
    }

    public static function getMailFields()
    {
        $fields = self::getFieldsValue();
        $return = [];
        foreach ($fields as $field => &$value) {
            if (method_exists(__NAMESPACE__ . '\\Fields\\' . $field, 'handleMailValue')) {
                $return[$field] = call_user_func([__NAMESPACE__ . '\\Fields\\' . $field, 'handleMailValue'], $value);
            } else {
                $return[$field] = null;
            }
        }
        return $return;
    }
}