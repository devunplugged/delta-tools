<?php

namespace DeltaTools\Url;

use Exception;

class Builder
{
    private $url = '';
    private $params = [];

    public function __construct(?string $url = null,?array $params = null)
    {
        $this->url = $url !== null ? $url : strtok($_SERVER["REQUEST_URI"], '?');
        $this->params = $params !== null ? $params : $_GET;
    }

    public function getParam($name)
    {
        if (!isset($this->params[$name])) {
            throw new Exception('No param found!');
        }

        return $this->params[$name];
    }

    public function setParam($name, $value)
    {
        // $this->params[$name] = $value;

        // return $this;

        if(is_array($value)){

            $this->params[$name] = [];

            foreach($value as $key => $val){
                $this->params[$name][$key] = $val;
            }

        }else{

            $this->params[$name] = $value;

        }

        return $this;
    }

    public function deleteParam($name)
    {
        if (!isset($this->params[$name])) {
            //throw new Exception('No param found! ('.$name.')');
            //stay silent (?)
            return $this;
        }

        unset($this->params[$name]);

        return $this;
    }

    public function getUrl(bool $full = true)
    {
        if(!$full){
            return $this->url;
        }

        $url = $this->url;
        $sign = '?';

        foreach($this->params as $paramName => $paramValue){

            if(is_array($paramValue)){

                foreach($paramValue as $key => $value){
                    $url .= $sign . $paramName . '['.$key.']=' . $value;
                    $sign = '&';
                }

            }else{
                $url .= $sign . $paramName . '=' . $paramValue;
                $sign = '&';
            }

            
        }

        return $url;
    }

    public function getParamsHiddenFields()
    {
        $fileds = '';
        foreach($this->params as $paramName => $paramValue){
            if(is_array($paramValue)){
                foreach($paramValue as $key => $value){
                    $fileds .= '<input type="hidden" name="'.htmlentities($paramName).'['.$key.']" value="'.htmlentities($value).'">';
                }
            }else{
                $fileds .= '<input type="hidden" name="'.htmlentities($paramName).'" value="'.htmlentities($paramValue).'">';
            }
            
        }
        return $fileds;
    }
}
