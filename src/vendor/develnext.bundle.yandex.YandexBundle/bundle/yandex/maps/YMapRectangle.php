<?php
namespace bundle\yandex\maps;

use bundle\yandex\maps\GeoObject;

/**
 * Прямоугольник
 * @url https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/Rectangle-docpage/    
 */
class YMapRectangle extends GeoObject
{
    public function __construct(float $lat1, float $lon1, float $lat2, float $lon2, array $properties = [], array $options = []){
        parent::__construct('Rectangle', [[$lat1, $lon1], [$lat2, $lon2]], $properties, $options);
    }
}