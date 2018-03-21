<?php

namespace isaactorresmichel\WordPress\WPstart;

class Utils
{
    public static function getConfigPairs($dir)
    {
        $pairs = [
            'SERVER_NAME',
            'ACCESS_LOG',
            'ERROR_LOG',
            'SOCKET_LOCATION'
        ];

        $return = [];
        foreach ($pairs as $pair) {
            $name = "%" . mb_strtolower($pair) . "%";
            $return[$name] = static::getEnv($pair);
        }

        return $return;
    }

    public static function loadEnv($dir)
    {
        $dotenv = new \Dotenv\Dotenv($dir);

        $pairs = [
            'SERVER_NAME',
            'ACCESS_LOG',
            'ERROR_LOG',
            'SOCKET_LOCATION'
        ];

        if ($dotenv->load()) {
            $dotenv->required($pairs);
        }
    }

    public static function getEnv($name)
    {
        return \Env::get($name);
    }
}
