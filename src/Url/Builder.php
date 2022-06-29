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
    }

    public function deleteParam($name)
    {
        if (!isset($this->params[$name])) {
            throw new Exception('No param found!');
        }

        unset($this->params[$name]);
    }

    public function getUrl()
    {
        $url = $this->url;
        $sign = '?';

        foreach($this->params as $paramName => $paramValue){
            $url .= $sign . $paramName . '=' . $paramValue;
            $sign = '&';
        }

        return $url;
    }
}