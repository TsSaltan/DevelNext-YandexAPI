<?php
namespace bundle\yandex\maps;

use bundle\yandex\YandexMap;
use php\gui\paint\UXColor;
use php\lib\str;
 
class GeoObject 
{
    /**
     * Имя переменной
     * @var string
     */
    private $varName;
    
    /**
     * @var YandexMap
     */
    protected $parentMap;
    
    protected $element,
              $geometry,
              $properties = [],
              $options = [];
              
    /**
     * Код, который будет выполнен после добавления объекта на карту
     * @var string
     */          
    protected $postCode;
    
    public function __construct(string $element, array $geometry, array $properties = [], array $options = []){
        $this->varName = 'obj_' . md5(str::uuid());
        $this->element = $element;
        $this->geometry = $geometry;
        $this->properties = $properties;
        $this->options = $options;
    }
    
    /**
     * Получить JS код для размещения объекта на карте
     */
    public function getCode() : string {
        return "var {$this->varName} = new ymaps." . str::upperFirst($this->element) . "(" . 
            json_encode($this->geometry) . ", " . 
            (sizeof($this->properties) > 0 ? json_encode($this->properties) : '{}') . ", " . 
            (sizeof($this->options) > 0 ? json_encode($this->options) : '{}') . ");";
    }    
    
    /**
     * Получить имя гренерированной переменной
     */
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
     
    /**
     * Открыть всплывающее окно 
     */ 
    public function openBalloon(){
        if($this->hasBalloon()){
            $this->executeScript($this->getVar() . '.balloon.open();');
        }
    } 
         
    /**
     * Установлено ли всплывающее сообщение 
     */     
    public function hasBalloon() : bool {
        return isset($this->properties['balloonContentBody']) || isset($this->properties['balloonContent']);
    } 
    
    /**
     * Установить ширину линии (обводки)
     */
    public function setStrokeWidth(int $width){
        $this->options['strokeWidth'] = $width;
    }    
    
    /**
     * Установить цвет линии (обводки)
     */
    public function setStrokeColor(UXColor $color){
        $this->options['strokeColor'] = $color->getWebValue();
    }    
    
    /**
     * Установить цвет заливки
     */
    public function setFillColor(UXColor $color){
        $this->options['fillColor'] = $color->getWebValue();
    }    
    
    /**
     * Установить прозрачность заливки
     */
    public function setFillOpacity(float $opacity){
        $this->options['fillOpacity'] = $opacity;
    }    
    
    /**
     * Установить прозрачность линии (обводки)
     */
    public function setStrokeOpacity(float $opacity){
        $this->options['strokeOpacity'] = $opacity;
    }    
    
    /**
     * Установить возможность перемещать объект
     */
    public function setDraggable(bool $draggable){
        $this->options['draggable'] = $draggable;
    }    
    
    /**
     * Установить закрашивание объекта
     */
    public function setFill(bool $fill){
        $this->options['fill'] = $fill;
    }   
     
    /**
     * Установить родительскую карту
     */
    public function setParentMap($map){
        $this->parentMap = $map;
        if(($this->parentMap instanceof YandexMap) and strlen($this->postCode) > 0){
            $this->parentMap->executeScript($this->postCode);
            $this->postCode = null;
        }
    }
         
    /**
     * Помещён ли объект на карту
     */
    public function hasParentMap() : bool {
        return is_object($this->parentMap) and ($this->parentMap instanceof YandexMap);
    }
    
    /**
     * Удалить объект
     */
    public function free(){
        if($this->hasParentMap()){
            $this->parentMap->removeObject($this);
        }
    }
    
    /**
     * Если объект размещён на карте, код будет выполнен прямо сейчас, иначе - после добавления на карту
     */
    protected function executeScript(string $script){
        if($this->hasParentMap()){
            $this->parentMap->executeScript($script);
        } else {
            $this->postCode .= "\n" . $script;
        }
    }

}