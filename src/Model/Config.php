<?php

namespace IPS\Model;

class Config
{
    protected static $values;

    public static function get($key, $key2 = null, $key3 = null)
    {
        if(empty(static::$values)) {
            $ips = include(__DIR__ . '/../../config/ips.php');
            $secure = include(__DIR__ . '/../../config/secure.php');

            static::$values = array_merge($ips, $secure);
        }

        if(is_null($key2)) {
            if(isset(static::$values[$key])) {
                return static::$values[$key];
            }
            return null;
        }

        if(is_null($key3)) {
            if(isset(static::$values[$key][$key2])) {
                return static::$values[$key][$key2];
            }
            return null;
        }

        if(isset(static::$values[$key][$key2][$key3])) {
            return static::$values[$key][$key2][$key3];
        }
        return null;
    }
}
