<?php
namespace bundle\yandex;

class YandexAPI 
{
    /**
     * Ключ для Яндекс.Карты
     * @url https://tech.yandex.ru/locator/
     */
    public static $mapKey;
    
    public static function setMapKey($key){
        self::$mapKey = $key;
    }
}