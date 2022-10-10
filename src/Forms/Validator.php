<?php

namespace DeltaTools\Forms;

class Validator
{
    private $generalErrors = [];
    private $validationErrors = [];
    private $allowedValidations = ['required', 'isNumber', 'numberRange'];
   
    public function once($once)
    {
        if (!\DeltaTools\Utilities\Forms\Once::check($once)) {
            $this->generalErrors[] = 'Ten formularz został już zapisany.';
            return false;
        }
        return true;
    }

    /**
     * Uruchamia wiele walidacji podanej wartosci
     */
    public function validate($key, $values, $validations = '', $validationArgs = [], $customMessages = [], $isGeneral = false )
    {
        $validations = explode('|', $validations);

        foreach($validations as $validation){
            if(!in_array($validation, $this->allowedValidations) || !method_exists($this, $validation)){
                continue;
            }

            if($validationArgs[$validation]){
                $this->$validation(
                    $key, 
                    $values, 
                    $customMessages[$validations] ?? '',
                    $isGeneral,
                    ...$validationArgs[$validation]
                );
            }else{
                $this->$validation(
                    $key, 
                    $values, 
                    $customMessages[$validations] ?? '',
                    $isGeneral
                );
            }
        }
    }

    /**
     * Sprawdza czy podana wartosc jest liczba (is_numeric)
     */
    public function required($key, $values, $customMessage = '', $isGeneral = false)
    {
        if (!isset($values[$key])) {

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? 'Ta wartość jest wymagana');
            }else{
                $this->generalErrors[] = $customMessage ?? $key . ': Ta wartość jest wymagana';
            }
            
            return false;
        }
        return true;
    }

    /**
     * Sprawdza czy podana wartosc jest liczba (is_numeric)
     */
    public function isNumber($key, $values, $customMessage = '', $isGeneral = false)
    {
        if (!is_numeric($values[$key])) {

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? 'Ta wartość powinna być liczbą');
            }else{
                $this->generalErrors[] = $customMessage ?? $key . ': Ta wartość powinna być liczbą';
            }
            
            return false;
        }
        return true;
    }

    /**
     * Sprawdza czy liczba miesci sie w przedziale
     */
    public function numberRange($key, $values, $customMessage = '', $isGeneral = false, $from = 'INF', $to = 'INF')
    {
        if (!$this->isNumber($key, $values[$key])) {
            return false;
        }

        if (
            ($from != 'INF' && $to == 'INF' && $values[$key] < $from) ||
            ($from == 'INF' && $to != 'INF' && $values[$key] > $to) ||
            ($from != 'INF' && $to != 'INF' && ($values[$key] > $to || $values[$key] < $from))
        ) {

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? 'Ta wartość powinna mieścić się w przedziale od ' . $from . ' do ' . $to);
            }else{
                $this->generalErrors[] = $customMessage ?? $key . ': Ta wartość powinna mieścić się w przedziale od ' . $from . ' do ' . $to;
            }
            return false;
        }

        return true;
    }

    public function getGeneralErrors()
    {
        return $this->generalErrors;
    }

    public function getGeneralErrorsList()
    {
        $list = '<ul>';
        foreach($this->generalErrors as $generalError){
            $list .= '<li>' . $generalError . '</li>';
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

    public function isValid()
    {
        if(!empty($this->generalErrors) || !empty($this->validationErrors)){
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
