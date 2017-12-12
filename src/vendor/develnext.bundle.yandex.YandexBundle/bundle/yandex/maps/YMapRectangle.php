<?php
namespace bundle\yandex\maps;

use bundle\yandex\maps\GeoObject;

/**
 * Прямоугольник
 * @url https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/Rectangle-docpage/    
 */
class YMapRectangle extends GeoObject
{
    public function __construct(array $coordinates, array $properties = [], array $options = []){
        parent::__construct('Rectangle', $coordinates, $properties, $options);
    }
}