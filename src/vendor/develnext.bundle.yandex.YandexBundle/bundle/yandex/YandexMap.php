<?php
namespace bundle\yandex;

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
     * @var callable 
     */
    private $onLoad;   
     
    /**
     * @var callable 
     */
    private $onClick;
    
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
                    if(is_callable($this->onLoad)){
                        waitAsync(1500, $this->onLoad);
                    }
                    break;
                
                case 'SCHEDULED':    
                case 'RUNNING':    
                    $this->webView->engine->cancel();
                    $this->webView->engine->refresh();
            }
        });
        
        $this->webView->on('click', function(){
            if(!is_callable($this->onClick)) return;
            // format data:lat;lon
            $data = $this->webView->engine->callFunction('getBridge', []);
            $ex = explode(':', $data);
            if(isset($ex[1]) and strlen($ex[1]) > 3){
                $pos = explode(';',$ex[1]);
                call_user_func_array($this->onClick, [floatval($pos[0]), floatval($pos[1])]);
            }
        });
    }
    
    public function onLoad(callable $callback){
        $this->onLoad = $callback;
    }    
    
    public function onClick(callable $callback){
        $this->onClick = $callback;
    }
    
    public function executeScript(string $script, $errorLevel = 0){
        try{
            if($this->webView->engine->state != 'SUCCEEDED'){
                throw new Exception('Browser does not ready!');       
            }    
            $this->webView->engine->executeScript($script);
        } catch (Exception | JSException $e) {
            if($errorLevel >= 3) return Logger::error('YandexMap: invalid script: "'.$script.'". Error: ' . $e->getMessage());
            waitAsync(1500, function() use ($script, $errorLevel){
                call_user_func_array([$this, 'executeScript'], [$script, $errorLevel+1]);
            });
        }
    }
    
    public function setCenter(float $lat, float $lon, int $zoom = 0){
        $json = json_encode([$lat, $lon]);
        return $this->executeScript("myMap.setCenter($json" . ($zoom > 0 ? ", $zoom" : ''). ")");
    }         
    /**
     * @param string $control 'zoomControl', 'searchControl', 'typeSelector',  'fullscreenControl', 'routeButtonControl' 
     */
    public function addControl(string $control){
        return $this->executeScript("myMap.controls.add('$control')");
    }      
    
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
    
    public function setBounds(float $lat1, float $lon1, float $lat2, float $lon2, array $options = []){
        $json = json_encode([[$lat1, $lon1], [$lat2, $lon2]]);
        $jOpts = sizeof($options) > 0 ? json_encode($options) : '{}';
        return $this->executeScript("myMap.setBounds($json, $jOpts)");
    }    
      
    public function setZoom(float $zoom){
        return $this->executeScript("myMap.setZoom($zoom)");
    }    
    
    /**
     * Установить тип
     * @param string $type = map | satellite | hybrid
     */
    public function setType(string $type){
        return $this->executeScript("myMap.setType('yandex#$type')");
    }
    
    public function addObject(GeoObject $object){
        //var_dump($object->getCode());
        $this->executeScript($object->getCode());
        return $this->executeScript("myMap.geoObjects.add(".$object->getVar().")");
    }     
    
    public function removeObject(GeoObject $object){
        return $this->executeScript("myMap.geoObjects.remove(".$object->getVar().")");
    } 
    
}