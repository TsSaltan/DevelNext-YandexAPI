<?php
namespace bundle\yandex\maps;

use php\lib\fs;
use php\lib\str;
use php\io\MemoryStream;
use php\gui\UXImage;
use bundle\yandex\maps\GeoObject;

/**
 * Метка
 * @url https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/Placemark-docpage/       
 */
class YMapPlacemark extends GeoObject
{
    public function __construct(float $lat, float $lon, $image, int $width = 0, int $height = 0, array $properties = [], array $options = []){
        $uximage = str::startsWith($image, 'http') ? UXImage::ofUrl($image) : new UXImage($image);
        $width = ($width == 0) ? $uximage->width : $width;
        $height = ($height == 0) ? $uximage->height : $height;
        unset($uximage);

        $base64 = 'data:image/png;base64,' . base64_encode(fs::get($image));
        
        $options['iconLayout'] = 'default#image';
        $options['iconImageHref'] = $base64;
        $options['iconImageSize'] = [$width, $height];
 
        parent::__construct('Placemark', [$lat, $lon], $properties, $options);
    }
}