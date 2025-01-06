<?php

namespace App\Cookies;

/**
 * Cookie manager
 */
class Cookies
{
    private static $storage = [];

    /**
     * Gets a cookie
     *
     * @param string $name
     *
     * @return string
     */
    public static function get($name)
    {
        if (isset(static::$storage[$name])) {
            return static::$storage[$name];
        }

        if (isset($_COOKIE[$name])) {
            static::$storage[$name] = $_COOKIE[$name];

            return $_COOKIE[$name];
        }
    }

    /**
     * Sets a cookie
     *
     * Cookies::set('name', 'leandrew', [
     *     'expires' => 100,
     *     'path' => '/',
     *     'domain' => '.dafabet.com',
     *     'secure' => true,
     *     'http' => true,
     * ]);
     *
     * @param string $name
     * @param string $value
     * @param array $options
     */
    public static function set($name, $value, $options = [])
    {
        $options += [
            'expire' => 0,
            'path' => null,
            'domain' => null,
            'secure' => false,
            'http' => false,
            'samesite' => null
        ];

        static::$storage[$name] = $value;

        return setcookie(
            $name,
            $value,
            $options['expire'],
            $options['path'] . '; samesite=' . $options['samesite'],
            $options['domain'],
            $options['secure'],
            $options['http']
        );
    }

    /**
     * Removes a cookie
     *
     * @param string $name
     */
    public static function remove($name, $options = [])
    {
        $options += [
            'expire' => 0,
            'path' => null,
            'domain' => null,
            'secure' => false,
            'http' => false,
            'samesite' => null
        ];

        setcookie(
            $name,
            null,
            time() - 3600,
            $options['path'] . '; samesite=' . $options['samesite'],
            $options['domain'],
            $options['secure'],
            $options['http']
        );

        unset(static::$storage[$name]);
    }
}
