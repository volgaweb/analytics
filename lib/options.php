<?php


namespace VW\Analytics;


use Bitrix\Main\Config\Option;
use Exception;

class Options
{
    public static $moduleId = "vw.analytics";

    public static function getOption(string $optionName)
    {
        if (empty($optionName)) {
            throw new Exception('$optionName cannot be empty');
        }
        return Option::get(self::$moduleId, $optionName, '', SITE_ID);
    }
}