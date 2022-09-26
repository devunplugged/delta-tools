<?php

namespace DeltaTools\Utilities\Response;

class Json
{
    public static function success(int $code = 200, array $data = [], string $message = 'success')
    {
        $code = ($code < 200 || $code > 299) ? 200 : $code;
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        $data['message'] = $message;
        $data['response_code'] = $code;

        echo json_encode($data);
        exit;
    }

    public static function fail(int $code, array $errors = [], int $customCode = 0, string $message = 'error')
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'message' => $message, 
            'response_code' => $code,
        ];

        if(!empty($errors)){
            $response['errors'] = $errors;
        }

        if($customCode !== 0){
            $response['error_code'] = $customCode;
        }

        echo json_encode($response);
        exit;
    }
}
