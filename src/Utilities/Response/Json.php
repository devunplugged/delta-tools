<?php

namespace DeltaTools\Utilities\Response;

class Json
{
    public static function success(array $data, int $code = 200)
    {
        $code = ($code < 200 || $code > 299) ? 200 : $code;
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public static function fail(string $message, int $code, array $errors = [], int $customCode = 0)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'message' => $message, 
            'code' => $code,
        ];

        if(!empty($errors)){
            $response['errors'] = $errors;
        }

        if($customCode !== 0){
            $response['customCode'] = $customCode;
        }

        echo json_encode($response);
        exit;
    }
}
