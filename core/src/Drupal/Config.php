<?php

namespace App\Drupal;

/**
 * Class for parsing custom drupal config
 */
class Config
{
    /**
     * This will parse any one dimentional (key => value) pair config
     *
     * @param string $config
     */
    public static function parse($config)
    {
        $nconfig = [];

        if (!empty($config)) {
            $rows = explode(PHP_EOL, $config);
            foreach ($rows as $rows) {
                $map = explode('|', trim($rows));
                if (!empty($map) && count($map) == 2) {
                    list($key, $value) = $map;
                    $nconfig[trim($key)] = trim($value);
                }
            }
        }

        return $nconfig;
    }

    /**
     * This will parse any two dimentional pair config as long they follow the
     * format
     *
     * Key|USD,RMB,BTC
     *
     * @param string $config
     */
    public static function parseCommaDelimited($config)
    {
        $result = [];
        $parsed = self::parse($config);

        foreach ($parsed as $key => $value) {
            $result[$key] = explode(',', trim($value));
        }

        return $result;
    }

    /**
     * Converts delimited string into multidimensional arrays
     *
     * Key|USD|something|value
     * Key|value
     * Key|something
     * value
     *
     * @param string $string
     * @param array $result
     */
    public static function parseMultidimensional($string)
    {
        $result = [];
        $lines = explode(PHP_EOL, $string);

        foreach ($lines as $line) {
            $entries = explode('|', rtrim($line));
            $count = count($entries);

            if ($count > 1) {
                $value = array_pop($entries);
                self::indexSet($result, $entries, $value);
                continue;
            }

            $line = trim($line);

            if (!empty($line)) {
                $result[] = $line;
            }
        }

        return $result;
    }

    /**
     * Mutates $value in the multidimensional array, at the specified $keys.
     * Creates subarrays if needed.
     *
     * @param array $array
     * @param array|ArrayObject $keys
     * @param mixed $value
     */
    private static function indexSet(&$array, $keys, $value)
    {
        $keys = (array) $keys;

        for ($i = &$array; null !== $key = array_shift($keys); $i = &$i[$key]) {
            if (!isset($i[$key])) {
                $i[$key] = [];
            }
        }

        $i = $value;
    }

    /**
     * Transform keys of collection to lowercase to
     * remove case sensitivity issue
     */
    static function lowercaseKeys($collection)
    {
        if (!$collection) {
            return [];
        }

        $result = [];

        foreach ($collection as $key => $value) {
            $result[strtolower($key)] = $value;
        }

        return $result;
    }
}
