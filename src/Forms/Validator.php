<?php

namespace DeltaTools\Forms;

class Validator
{
    private $generalErrors = [];
    private $validationErrors = [];

    public function getGeneralErrors()
    {
        return $this->generalErrors;
    }

    public function getGeneralErrorsList()
    {
        $list = '<ul>';
        foreach($this->generalErrors as $generalError){
            $list .= $generalError;
        }
        return $list . '</ul>';
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    public function getValidationErrorList($key)
    {
        if(!isset($this->validationErrors[$key])){
            return;
        }
        $list = '<ul>';
        foreach($this->validationErrors[$key] as $validationError){
            $list .= $validationError;
        }
        return $list . '</ul>';
    }
    
    public function once($once)
    {
        if (!\DeltaTools\Utilities\Forms\Once::check($once)) {
            $this->generalErrors[] = 'Ten formularz został już zapisany.';
            return false;
        }
        return true;
    }

    public function isNumber($key, $value)
    {
        if (!is_numeric($value)) {
            $this->addValidationError($key, 'Ta wartość powinna być liczbą');
            return false;
        }
        return true;
    }

    public function isValid()
    {
        if(!empty($this->generalErrors) || !empty($this->validationErrors)){
            return false;
        }
        return true;
    }

    public function numberRange($key, $value, $from = 'INF', $to = 'INF')
    {
        if (!$this->isNumber($key, $value)) {
            return false;
        }

        if (
            ($from != 'INF' && $to == 'INF' && $value < $from) ||
            ($from == 'INF' && $to != 'INF' && $value > $to) ||
            ($from != 'INF' && $to != 'INF' && ($value > $to || $value < $from))
        ) {
            $this->addValidationError($key, 'Ta wartość powinna mieścić się w przedziale od ' . $from . ' do ' . $to);
            return false;
        }

        return true;
    }

    private function addValidationError($key, $msg)
    {
        if (isset($this->validationErrors[$key])) {
            $this->validationErrors[$key][] = $msg;
        } else {
            $this->validationErrors[$key] = [$msg];
        }
    }
}
