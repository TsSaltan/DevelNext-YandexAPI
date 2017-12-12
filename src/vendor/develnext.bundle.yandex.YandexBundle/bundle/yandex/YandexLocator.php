<?php
namespace bundle\yandex;

use php\lib\str;
use php\gui\framework\AbstractScript;
use bundle\yandex\YandexAPI;
use bundle\yandex\YandexException;
use bundle\jurl\jURL;

/**
 * Определение координат
 * @required jURL bundle
 */
class YandexLocator
{
   
    private static $apiUrl = 'http://api.lbs.yandex.net/geolocation';
    
    /**
     * Получить местоположение по wifi точкам
     * @param  array $networks Массив точек вида [['mac' => string, 'signal_strength' => int], ]
     * @throws YandexException
     * @return array
     */
    public static function getLocationByWifi(array $networks) : array {
        return self::query(['wifi_networks' => $networks]);
    }    
    
    /**
     * Местоположение по ip
     * @param string $ip Если null - собственный ip
     */
    public static function getLocationByIP(string $ip = null) : array {      
        if(is_null($ip)) $q = [];
        elseif(str::contains('.') and !str::contains(':')) $q = ['ip' => ['address_v4' => $ip]];
        else $q = ['ip' => ['address_v6' => $ip]];
        return self::query($q);
    }

    private static function query(array $data){
        // var_dump(['query' => $data]);
        $data['common']['version'] = "1.0";
        $data['common']['api_key'] = YandexAPI::$mapKey;
        $ch = new jURL(self::$apiUrl);
        $ch->setRequestMethod('POST');
        $ch->setPostData(['json' => json_encode($data)]);
        
        $answer = $ch->exec();
        $result = json_decode($answer, true);
        
        if(isset($result['position'])) return $result['position'];
        if(isset($result['error'])) throw new YandexException('Query error: ' . $result['error']);

        throw new YandexException('Invalid query: ' . var_export($data, true) . "\nServer returns: " . $answer);
    }
}