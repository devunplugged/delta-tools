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
     * 
     * np. validationsArray
     * 
     * [
     * 
     *      ['supplier_id', 'required|isNumber', true, [], ['required' => 'supplier_id jest wymagane','isNumber' => 'supplier_id musi byc liczbą']],
     * 
     *      ['type' , 'isEnum', true, ['isEnum' => ['long','short']], ['isEnum' => 'type musi być jedną z dozwolonych warotści']]
     * 
     * ]
     * 
     * Zazwyczaj wymagane jest podanie tylko dwóch elementów: klucza do sprawdzenia i metody walidacji, czasem wymagane jest tez podanie argumentow dla metody walidacyjnej jak np przy isEnum
     * 
     * [
     * 
     *      ['supplier_id', 'required|isNumber'],
     * 
     *      ['type' , 'isEnum', false, ['isEnum' => ['long','short']]]
     * 
     * ]
     */
    public function validate(array $values, array $validationsArray )
    {
        foreach($validationsArray as $validationElement){

            if(!isset($validationElement[0])){
                throw new \Exception('Brak podanego klucza tablicy do sprawdzenia');
            }
        
            if(!isset($validationElement[1])){
                throw new \Exception('Brak podanej funkcji walidacyjnej');
            }

            $this->validateElement($values, ...$validationElement);
        }
    }

    /**
     * Uruchamia wiele walidacji podanej wartosci tablicy $values
     */
    public function validateElement($values, $key, $validations = '', $isGeneral = false, $validationArgs = [], $customMessages = [] )
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
    public function required(string $key, array $values, ?string $customMessage = null, bool $isGeneral = false):bool
    {
        if (isset($values[$key])) {
            return true;
        }
        
        if(!$isGeneral){
            $this->addValidationError($key, $customMessage ?? "$key jest wartością wymaganą");
        }else{
            $this->generalErrors[] = $customMessage ?? "$key jest wartością wymaganą";
        }
        
        return false;
    }

    /**
     * Sprawdza czy podana wartosc jest liczba (is_numeric)
     */
    public function isNumber(string $key, array $values, ?string $customMessage = null, bool $isGeneral = false):bool
    {
        //jesli nie istnieje to ok; mogl nie byc wymagany
        if(!isset($values[$key])){
            return true;
        }

        if (is_numeric($values[$key])) {
            return true; 
        }

        if(!$isGeneral){
            $this->addValidationError($key, $customMessage ?? "$key musi być liczbą");
        }else{
            $this->generalErrors[] = $customMessage ??  "$key musi być liczbą";
        }
        
        return false;
    }

    /**
     * Sprawdza czy liczba miesci sie w przedziale
     */
    public function numberRange(string $key, array $values, ?string $customMessage = null, bool $isGeneral = false, array $range = ['INF','INF']):bool
    {
        //jesli nie istnieje to ok; mogl nie byc wymagany
        if(!isset($values[$key])){
            return true;
        }

        if(!isset($range[0]) || !isset($range[1])){
            throw new \Exception('Brak podanych zakresów do sprawdzenia');
        }

        if (
            ($range[0] != 'INF' && $range[1] == 'INF' && $values[$key] < $range[0]) ||
            ($range[0] == 'INF' && $range[1] != 'INF' && $values[$key] > $range[1]) ||
            ($range[0] != 'INF' && $range[1] != 'INF' && ($values[$key] > $range[1] || $values[$key] < $range[0]))
        ) {

            if(!$isGeneral){
                $this->addValidationError($key, $customMessage ?? "$key musi mieścić się w przedziale od $range[0] do $range[1]");
            }else{
                $this->generalErrors[] = $customMessage ?? "$key musi mieścić się w przedziale od $range[0] do $range[1]";
            }
            return false;
        }

        return true;
    }

    /**
     * Sprawdza czy podana wartosc jest ciagiem znakow z puli
     */
    public function isEnum(string $key, array $values, ?string $customMessage = null, bool $isGeneral = false, array $enums = []):bool
    {
        //jesli nie istnieje to ok; mogl nie byc wymagany
        if(!isset($values[$key])){
            return true;
        }

        if(empty($enums)){
            throw new \Exception('isEnum: Brak wartości do testu');
        }

        if(in_array($values[$key], $enums)){
            return true;
        }

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

    /**
     * Sprawdza czy podana wartość jest tablicą
     */
    public function isArray(string $key, array $values, ?string $customMessage = null, bool $isGeneral = false)
    {
        if(is_array($values[$key])){
            return true;
        }

        if(!$isGeneral){
            $this->addValidationError($key, $customMessage ?? "$key musi być tablicą");
        }else{
            $this->generalErrors[] = $customMessage ?? "$key musi być tablicą";
        }

        return false;
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
