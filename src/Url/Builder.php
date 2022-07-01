<?php

namespace DeltaTools\Url;

use Exception;

class Builder
{
    private $url = '';
    private $params = [];

    public function __construct()
    {
        $this->url = strtok($_SERVER["REQUEST_URI"], '?');
        $this->params = $_GET;
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
        $this->params[$name] = $value;

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
            $url .= $sign . $paramName . '=' . $paramValue;
            $sign = '&';
        }

        return $url;
    }

    public function getParamsHiddenFields()
    {
        $fileds = '';
        foreach($this->params as $paramName => $paramValue){
            if(is_array($paramValue)){
                foreach($paramValue as $value){
                    $fileds .= '<input type="hidden" name="'.htmlentities($paramName).'[]" value="'.htmlentities($value).'">';
                }
            }else{
                $fileds .= '<input type="hidden" name="'.htmlentities($paramName).'" value="'.htmlentities($paramValue).'">';
            }
            
        }
        return $fileds;
    }
}
