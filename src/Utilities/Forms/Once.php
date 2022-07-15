<?php
namespace DeltaTools\Utilities\Forms;

class Once
{
    public static function get()
    {
        $_SESSION['form-once'] = \DeltaTools\Utilities\Random\Generator::simple();
    }

    public static function check($once)
    {
        $check = $_SESSION['form-once'] == $once;
        self::get();
        return $check;
    }
}