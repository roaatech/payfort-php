<?php

namespace ItvisionSy\Payment\PayFort;

class Loader
{

    public static function init()
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . "helpers.php";
        static::register();
    }

    public static function autoloader($className)
    {

        $thisParts = array_slice(explode("\\", self::class), 0, -1);
        $requestedParts = array_slice(explode("\\", $className), 0, count($thisParts));
        if (count($requestedParts) != count($thisParts) || $requestedParts !== $thisParts) {
            return false;
        }
        $extraPath = str_replace("\\", DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . implode("\\", array_slice(explode("\\", $className), count($thisParts)))) . ".php";
        $path = __DIR__ . $extraPath;
        if (file_exists($path)) {
            require_once $path;
            return;
        }
        return false;
    }

    public static function register()
    {
        spl_autoload_register([static::class, 'autoloader'], false, true);
    }

}
