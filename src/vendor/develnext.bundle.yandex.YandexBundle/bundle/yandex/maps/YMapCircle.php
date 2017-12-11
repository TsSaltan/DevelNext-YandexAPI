<?php
namespace bundle\yandex\maps;

use bundle\yandex\maps\GeoObject;

/**
 * Круг
 * @url https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/geometry.Circle-docpage/       
 */
class YMapCircle extends GeoObject
{
    public function __construct(float $lat, float $lon, int $radius, array $options = []){
        parent::__construct('Circle', [[$lat, $lon], $radius], [], $options);
    }
}