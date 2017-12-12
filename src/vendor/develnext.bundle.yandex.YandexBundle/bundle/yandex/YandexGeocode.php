<?php
namespace bundle\yandex;

use bundle\jurl\jURL;
use bundle\yandex\YandexAPI;

class YandexGeocode 
{
    private static $apiUrl = 'https://geocode-maps.yandex.ru/1.x/';
    
    private static $lang = 'ru_RU';
    
    /**
     * Установить язык ответа: ru_RU — русский (по умолчанию), uk_UA — украинский, be_BY — белорусский, en_RU — американский, en_US — американский английский, tr_TR — турецкий (только для карты Турции)
     */
    public static function setLang(string $lang){
        self::$lang = $lang;
    }
    
    /**
     * Получить адрес по координатам 
     * @return array
     */
    public static function getAddressByCoords(float $lat, float $lon) : array {
        $params = [
            'sco' => 'latlong',
            'geocode' => $lat . ', ' . $lon
        ];
        
        return self::query($params);
    }
    
    private static function query(array $params){
        $params['lang'] = self::$lang;
        $params['format'] = 'json';
        $params['key'] = YandexAPI::$mapKey;
        var_dump($url = self::$apiUrl . '?' . http_build_query($params));
        $ch = new jURL($url);
        $data = $ch->exec();
        $json = json_decode($data, true);
        
        return $json['response']['GeoObjectCollection']['featureMember'] ?? $json;
    }
}