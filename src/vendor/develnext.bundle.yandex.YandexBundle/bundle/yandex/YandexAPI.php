<?php
namespace bundle\yandex;

class YandexAPI 
{
    // API key -> https://tech.yandex.ru/locator/
    public static $apiKey;
    
    public static function setKey($key){
        self::$apiKey = $key;
    }
}