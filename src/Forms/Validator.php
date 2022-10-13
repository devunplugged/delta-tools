<?php

namespace DeltaTools\Forms;

class Validator
{
    private $generalErrors = [];
    private $validationErrors = [];
    protected $allowedValidations = ['required', 'isNumber', 'numberRange', 'isEnum'];
   
    public function once($once)
    {
        if (!\DeltaTools\Utilities\Forms\Once::check($once)) {
            $this->generalErrors[] = 'Ten formularz został już zapisany.';
            return false;
        }
        return true;
    }

    /**
     * Uruchamia wiele walidacji dla wielu podanych zmiennych (kluczy tabeli $values)
     * np. validationsArray
     * [
     *      ['supplier_id', 'required|isNumber', ['required' => 'supplier_id jest wymagane','isNumber' => 'supplier_id musi byc liczbą'], [], true]
     *      .
     *      .
     *      .
     * ]
     */
    public function validate($values, $validationsArray )
    {
        foreach($validationsArray as $validationElement){
            $this->validateElement($validationElement[0], $values, $validationElement[1], $validationElement[2], $validationElement[3], $validationElement[4]);
        }
    }

    /**
     * Uruchamia wiele walidacji podanej wartosci tablicy $values
     */
    public function validateElement($key, $values, $validations = '', $customMessages = [], $validationArgs = [], $isGeneral = false )
    {
        $validations = explode('|', $validations);

        foreach($validations as $validation){
            if(!in_array($validation, $this->allowedValidations) || !method_exists($this, $validation)){
                continue;
            }

            if(isset($validationArgs[$validation])){
                $this->$validation(
                    $key, 
                    $values, 
                    $customMessages[$validation] ?? null,
                    $isGeneral,
                    $validationArgs[$validation]
                );
            }else{
                $this->$validation(
                    $key, 
                    $values, 
                    $customMessages[$validation] ?? null,
                    $isGeneral
                );
            }
        }
    }

    /**
     * Sprawdza czy podana wartosc jest liczba (is_numeric)
     */
    public function required($key, $values, $customMessage = null, $isGeneral = false)
    {
        if (!isset($values[$key])) {

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? "$key jest wartością wymaganą");
            }else{
                $this->generalErrors[] = $customMessage ?? "$key jest wartością wymaganą";
            }
            
            return false;
        }
        return true;
    }

    /**
     * Sprawdza czy podana wartosc jest liczba (is_numeric)
     */
    public function isNumber($key, $values, $customMessage = null, $isGeneral = false)
    {
        //jesli nie istnieje to ok; mogl nie byc wymagany
        if(!isset($values[$key])){
            return true;
        }

        if (!is_numeric($values[$key])) {

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? "$key musi być liczbą");
            }else{
                $this->generalErrors[] = $customMessage ??  "$key musi być liczbą";
            }
            
            return false;
        }
        return true;
    }

    /**
     * Sprawdza czy liczba miesci sie w przedziale
     */
    public function numberRange($key, $values, $customMessage = null, $isGeneral = false, $from = 'INF', $to = 'INF')
    {
        //jesli nie istnieje to ok; mogl nie byc wymagany
        if(!isset($values[$key])){
            return true;
        }

        if (
            ($from != 'INF' && $to == 'INF' && $values[$key] < $from) ||
            ($from == 'INF' && $to != 'INF' && $values[$key] > $to) ||
            ($from != 'INF' && $to != 'INF' && ($values[$key] > $to || $values[$key] < $from))
        ) {

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? "$key musi mieścić się w przedziale od $from do $to");
            }else{
                $this->generalErrors[] = $customMessage ?? "$key musi mieścić się w przedziale od $from do $to";
            }
            return false;
        }

        return true;
    }

    /**
     * Sprawdza czy podana wartosc jest ciagiem znakow z puli
     */
    public function isEnum($key, $values, $customMessage = null, $isGeneral = false, $enums = [])
    {
        //jesli nie istnieje to ok; mogl nie byc wymagany
        if(!isset($values[$key])){
            return true;
        }

        if(empty($enums)){
            throw new \Exception('isEnum: Brak wartości do testu');
        }

        if(!in_array($values[$key], $enums)){
            $allowed = '';
            foreach($enums as $enum){
                $allowed .= $enum.',';
            }
            $allowed = rtrim($allowed, ',');

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? "$key musi być jedną z dozwolonych wartości: $allowed");
            }else{
                $this->generalErrors[] = $customMessage ?? "$key musi być jedną z dozwolonych wartości: $allowed";
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
