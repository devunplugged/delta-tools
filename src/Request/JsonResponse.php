<?php

namespace DeltaTools\Request;

class JsonResponse
{
    private $responseCode = 200;

    public function setResponseCode(int $code)
    {
        $this->responseCode = $code;
    }

    public function send($data)
    {
        http_response_code($this->responseCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
