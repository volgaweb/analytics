<?php

namespace VW\Analytics;

class Handlers
{
    public static function OnBeforeEventSend(&$arFields, $arTemplate)
    {
        if (strpos($arTemplate['EVENT_NAME'], 'FORM_FILLING') !== false) {
            Webform::handlerFormWebformFields($arFields);
        }
    }
}