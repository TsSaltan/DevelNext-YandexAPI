<?php
namespace bundle\yandex\maps;

use php\lib\str;
 
class GeoObject 
{
    private $varName;
    private $jsCode;
    
    public function __construct(string $element, array $geometry, array $properties = [], array $options = []){
        $this->varName = 'obj' . str::random(5, 'abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $this->jsCode = "var {$this->varName} = new ymaps." . str::upperFirst($element) . "(" . 
            json_encode($geometry) . ", " . 
            (sizeof($properties) > 0 ? json_encode($properties) : '{}') . ", " . 
            (sizeof($options) > 0 ? json_encode($options) : '{}') . ");";
    }
    
    public function getCode() : string {
        return $this->jsCode;
    }    
    
    public function getVar() : string {
        return $this->varName;
    }
}