<?php


namespace App\Util;


class Controllers
{
    public static function get(string $class, string $controllerMethod): string
    {
        return "$class::$controllerMethod";
    }
}
