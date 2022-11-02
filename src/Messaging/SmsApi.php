<?php
namespace DeltaTools\Messaging;

/**
 * Klasa do wysylania wiadomosci sms przez usługę smsapi
 * 
 * Użycie:
 * 
 * require MODROOT . 'admin/lib/smsapi.inc';
 * $sms = new Sms();
 * $sms->setTo('607123123');
 * $sms->setMessage('Przykladowa wiadomosc');
 * $sms->send();
 */

class SmsApi
{
    //URL punktu koncowego smsapi
    private $url = 'https://api.smsapi.pl/sms.do';

    //login do uslugi smsapi
    private $username = SMS_API_LOGIN;
	
	//$token do uslugi smsapi
	//private $token = '';

    //haslo do uslugi smsapi
    private $password = SMS_API_PASS;

    //nadawca wiadomości
    private $from = 'DELTA-OPTI';

    //odbiorca wiadomości
    private $to = '';

    //wiadomość typu eco; z losowego numeru bez uwzglednienia $from (tansza wersja)
    private $eco = 0;

    //wiadomość typu eco; z losowego numeru bez uwzglednienia $from (tansza wersja)
    private $message = '';

    //odpowiedz od serwera smsapi
    private $response = null;

    //kod odpowiedzi serwera smsapi
    private $responseCode = null;


    public function setFrom($from)
    {
        $this->from = trim($from);
    }

    public function setTo($to)
    {
        $this->to = trim($to);
    }

    public function setEco(int $eco)
    {
        $this->eco = $eco == 0 ? 0 : 1;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getResponseCode()
    {
        return $this->responseCode;
    }

    private function checkParams()
    {
        if(!$this->url){
            throw new \Exception("Nie można wysłać wiadomości sms bez podania adresu usługi");
        }

        if(!$this->username){
            throw new \Exception("Nie można wysłać wiadomości sms bez podania loginu do usługi");
        }

        if(!$this->password){
            throw new \Exception("Nie można wysłać wiadomości sms bez podania hasła do usługi");
        }

        if(!$this->from && !$this->eco){
            throw new \Exception("Nie można wysłać wiadomości sms pro bez podania nadawcy");
        }

        if(!$this->to){
            throw new \Exception("Nie można wysłać wiadomości sms bez podania odbiorcy");
        }

        if(!$this->message){
            throw new \Exception("Nie można wysłać wiadomości sms bez podania treści wiadomości");
        }
    }

    private function getParams()
    {
        $this->checkParams();
        $params = [];
        $params['username'] = $this->username;
        $params['password'] = md5($this->password);//)
        $params['to'] = $this->to;
        $params['from'] = $this->from;
        $params['eco'] = $this->eco;
        $params['message'] = $this->message;
        return $params;
    }

    public function send()
    {
        $this->clearResponse();

        $curl = curl_init($this->url);
		curl_setopt($curl, CURLOPT_URL, $this->url);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->getParams()));
        //for debug only!
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $this->response = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if(strpos($this->response, 'OK') !== false){
            return true;
        }
        return false;
    }

    private function clearResponse()
    {
        $this->response = null;
        $this->responseCode = null;
    }
}