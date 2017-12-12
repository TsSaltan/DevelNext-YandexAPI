<?php
namespace bundle\yandex\maps;

use php\gui\paint\UXColor;
use php\lib\str;
 
class GeoObject 
{
    private $varName;
    protected $element,
              $geometry,
              $properties = [],
              $options = [];
    
    public function __construct(string $element, array $geometry, array $properties = [], array $options = []){
        $this->varName = 'obj_' . md5(str::uuid());
        $this->element = $element;
        $this->geometry = $geometry;
        $this->properties = $properties;
        $this->options = $options;
    }
    
    public function getCode() : string {
        return "var {$this->varName} = new ymaps." . str::upperFirst($this->element) . "(" . 
            json_encode($this->geometry) . ", " . 
            (sizeof($this->properties) > 0 ? json_encode($this->properties) : '{}') . ", " . 
            (sizeof($this->options) > 0 ? json_encode($this->options) : '{}') . ");";
    }    
    
    public function getVar() : string {
        return $this->varName;
    }    
    
    /**
     * Установить содержимое всплывающего окна, появляющегося при клике
     */
    public function setBalloon(string $header, string $body, string $footer){            
        $this->properties['balloonContentHeader'] = $header;
        $this->properties['balloonContentBody'] = $body;
        $this->properties['balloonContentFooter'] = $footer;
    }  
        
    /**
     * Установить содержимое всплывающего окна, появляющегося при клике
     */
    public function setBalloonContent(string $content){            
        $this->properties['balloonContent'] = $content;
    }  
        
    /**
     * Установить содержимое подсказки, появляющегося при наведении
     */    
    public function setHintContent(string $content){
        $this->properties['hintContent'] = $content;
    }      
     
    public function setStrokeWidth(int $width){
        $this->options['strokeWidth'] = $width;
    }    
    
    public function setStrokeColor(UXColor $color){
        $this->options['strokeColor'] = $color->getWebValue();
    }    
    
    public function setFillColor(UXColor $color){
        $this->options['fillColor'] = $color->getWebValue();
    }    
    
    public function setFillOpacity(float $opacity){
        $this->options['fillOpacity'] = $opacity;
    }    
    
    public function setStrokeOpacity(float $opacity){
        $this->options['strokeOpacity'] = $opacity;
    }    
    
    public function setDraggable(bool $draggable){
        $this->options['draggable'] = $draggable;
    }    
    
    public function setFill(bool $fill){
        $this->options['fill'] = $fill;
    }
}