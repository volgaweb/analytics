<?php

namespace VW\Analytics\Fields;

use Bitrix\Main\Context;
use VW\Analytics\Options;

class yametrika extends baseField implements abstractField
{
    private static $cookieName = '_ym_uid';

    // получаем куку в которой хранится YM ClientID
    public static function getData()
    {
        $result = Context::getCurrent()->getRequest()->getCookieRaw(self::$cookieName);
        return $result ?: null;
    }

    // подменяем значение в почтовом шаблоне. в данном случае нам нужна ссылка на запись в метрике
    public static function handleMailValue(?string $value)
    {
        //проверка что значение не пустое, изначально приходит пробел
        if (mb_strlen($value) !== 1) {
            $metrikaId =  Options::getOption('ymetrika');
            if ($metrikaId) {
                $url = "https://metrika.yandex.ru/stat/visitors?period=year&filter=(EXISTS+ym%3Au%3AuserID+WITH+(ym%3Au%3AclientID%3D%3D%2527$value%2527))&id=$metrikaId";
                $value .= "\rДля просмотра Метрики воспользуйтесь ссылкой: \r$url";
            } else {
                $value = $value . "\nОтсутствует ссылка так как не настроен id метрики в модуле аналитики";
            }
        } else {
            $value = "У пользователя заблокирована метрика или не подключена на сайте";
        }
        return (string)$value;
    }
}
