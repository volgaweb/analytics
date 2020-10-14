<?php


namespace VW\Analytics;


class Options
{
    public static $moduleId = "vw.analytics";

    public static function getOption(string $optionName)
    {
        if (empty($optionName)) {
            throw new \Exception('$optionName cannot be empty');
        }
        return \Bitrix\Main\Config\Option::get(self::$moduleId, $optionName, '', SITE_ID);
    }
}