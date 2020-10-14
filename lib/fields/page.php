<?php

namespace VW\Analytics\Fields;

use Bitrix\Main\Context;

class page extends baseField implements abstractField
{
    public static function getData()
    {
        $result = Context::getCurrent()->getServer()->get('HTTP_REFERER');
        return $result ?: null;
    }
}