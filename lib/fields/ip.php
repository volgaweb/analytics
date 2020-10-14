<?php

namespace VW\Analytics\Fields;

use Bitrix\Main\Context;

class ip extends baseField implements abstractField
{
    public static function getData()
    {
        $result = Context::getCurrent()->getServer()->get('REMOTE_ADDR');
        return $result ?: null;
    }
}