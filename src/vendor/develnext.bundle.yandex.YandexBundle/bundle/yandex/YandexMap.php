<?php
namespace bundle\yandex;

use php\time\Timer;
use bundle\yandex\maps\GeoObject;
use php\framework\Logger;
use php\lang\Thread;
use php\gui\JSException;
use Exception;
use php\lib\fs;
use php\gui\UXWebView;
use php\gui\layout\UXPanel;

class YandexMap
{
    /**
     * @var UXWebView 
     */
    private $webView;    
    
    /**
     * @var UXPanel 
     */
    private $parentPanel;    
    
    /**
     * @var bool 
     */
    private $isLoad = false;   
     
    /**
     * @var array 
     */
    private $events = [];
    
    private $indexPage = 'res://bundle/yandex/maps/index.html';
    
    public function __construct(UXPanel $parent){
        $this->webView = new UXWebView;        
        $this->parentPanel = $parent;
                
        $this->webView->cache = true;   
        $this->webView->bottomAnchor =    
        $this->webView->topAnchor =    
        $this->webView->leftAnchor =    
        $this->webView->rightAnchor = 0;
           
        $this->webView->engine->loadContent(fs::get($this->indexPage), 'text/html');  
        $this->parentPanel->add($this->webView);
        $this->webView->engine->watchState(function($browser, $a, $b){
            switch($b){
                case 'SUCCEEDED':
                    // После загрузки карты запускаем обработчик событий
                    $this->eventHandler();
                    break;
                
                case 'SCHEDULED':    
                case 'RUNNING':    
                    $this->webView->engine->cancel();
                    $this->webView->engine->refresh();
            }
        });
    }
    
    /**
     * Добавить обработчик событий
     * @url https://tech.yandex.ru/maps/doc/jsapi/2.0/dg/concepts/events-docpage/ 
     */
    public function on(string $event, callable $callback){
        $this->events[$event] = $callback;
        $this->executeScript('registerMapEvent("' . $event . '")');
    }    
    
    /**
     * Удалить обработчик событий
     */
    public function off(string $event){
        unset($this->events[$event]);
    }
    
    private function eventHandler(){
        Timer::setInterval(function(){
            uiLaterAndWait(function(){
                $data = json_decode($this->webView->engine->title, true);
                if(!isset($data['event'])) return;
                if($data['event'] == 'load'){
                    $this->isLoad = true;
                }
                
                $this->executeScript('Bridge.clear()');
                if(isset($this->events[$data['event']])){
                    call_user_func_array($this->events[$data['event']], [$data]);
                }
            });
        }, 250);
    }
    
    /**
     * Выполнить JavaScript
     */
    public function executeScript(string $script){
        if($this->webView->engine->state != 'SUCCEEDED' || !$this->isLoad){
            return waitAsync(1000, function() use ($script, $errorLevel){
                call_user_func_array([$this, 'executeScript'], [$script]);
            });     
        }     
            
        try{   
            $this->webView->engine->executeScript($script);
        } catch (Exception | JSException $e) {
            return Logger::error('YandexMap: invalid script: "'.$script.'". Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Установить центр карты
     */
    public function setCenter(float $lat, float $lon, int $zoom = 0){
        $json = json_encode([$lat, $lon]);
        return $this->executeScript("myMap.setCenter($json" . ($zoom > 0 ? ", $zoom" : ''). ")");
    }         
    
    /**
     * Добавить элемент управления
     * @param string $control = 'zoomControl', 'searchControl', 'typeSelector',  'fullscreenControl', 'routeButtonControl' 
     */
    public function addControl(string $control){
        return $this->executeScript("myMap.controls.add('$control')");
    }      
    
    /**
     * Добавить несколько элементов управления 
     */
    public function addControls(array $controls){
        array_map([$this, 'addControl'], $controls);
    }     
    
    /**
     * Плавное перемещение по координатам 
     */
    public function panTo(float $lat, float $lon, int $delay = 1000){
        $json = json_encode([$lat, $lon]);
        return $this->executeScript("myMap.panTo($json, {delay: $delay})");
    }      
    
    /**
     * Сделать видимыми границы 
     */
    public function setBounds(float $lat1, float $lon1, float $lat2, float $lon2, array $options = []){
        $json = json_encode([[$lat1, $lon1], [$lat2, $lon2]]);
        $jOpts = sizeof($options) > 0 ? json_encode($options) : '{}';
        return $this->executeScript("myMap.setBounds($json, $jOpts)");
    }    
      
    /**
     * Установить степень увеличения 
     */  
    public function setZoom(int $zoom){
        return $this->executeScript("myMap.setZoom($zoom)");
    }    
    
    /**
     * Установить тип
     * @param string $type = map | satellite | hybrid
     */
    public function setType(string $type){
        return $this->executeScript("myMap.setType('yandex#$type')");
    }
    
    /**
     * Добавить объект на карту 
     */
    public function addObject(GeoObject $object){
        $this->executeScript($object->getCode());
        $this->executeScript("myMap.geoObjects.add(".$object->getVar().")");
        $object->setParentMap($this);
    }     
    
    /**
     * Удалить объект 
     */
    public function removeObject(GeoObject $object){
        $this->executeScript("myMap.geoObjects.remove(".$object->getVar().")");
    } 
    
}