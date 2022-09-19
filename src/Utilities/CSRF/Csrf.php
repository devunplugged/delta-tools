<?php
namespace DeltaTools\Utilities\CSRF;

class Csrf
{
    public static function generate()
    {
        $_SESSION['csrf'] = \DeltaTools\Utilities\Random\Generator::secure(32);
        return $_SESSION['csrf'];
    }

    public static function check($token)
    {
        return $_SESSION['csrf'] == $token;
    }
}