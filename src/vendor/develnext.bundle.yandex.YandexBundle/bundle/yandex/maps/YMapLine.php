<?php
namespace bundle\yandex\maps;

use php\gui\paint\UXColor;
use bundle\yandex\maps\GeoObject;

/**
 * Ломаная линия
 * @url https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/geometry.LineString-docpage/       
 */
class YMapLine extends GeoObject
{
    public function __construct(array $coordinates, array $properties = [], array $options = []){
        parent::__construct('Polyline', $coordinates, $properties, $options);
    }
}