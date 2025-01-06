<?php

namespace App\Async\Utils;

class ArrayStructure
{
    /**
     *
     */
    public static function flatten($array, $prefix = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + self::flatten($value, "$prefix$key.");
            } else {
                $result["$prefix$key"] = $value;
            }
        }

        return $result;
    }

    /**
     *
     */
    public function unflatten($collection)
    {
        $collection = (array) $collection;
        $output = [];

        foreach ($collection as $key => $value) {
            self::arraySet($output, $key, $value);

            if (is_array($value) && !strpos($key, '.')) {
                $nested = self::unflatten($value);
                $output[$key] = $nested;
            }
        }

        return $output;
    }

    /**
     *
     */
    public static function arraySet(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}
