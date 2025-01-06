<?php

namespace App\Extensions\Form\Validators;

use Symfony\Component\Yaml\Yaml;
use Prophecy\Promise\ThrowPromise;

/**
 * Default webform validator methods
 */
class Validators
{
    /**
     * The default validation should always be true
     */
    public function default($value, $param, $field)
    {
        return true;
    }

    /**
     * Required validation
     */
    public function required($value, $param, $field)
    {
        $value = is_array($value) ? reset($value) : $value;

        return strlen(strval($value)) > 0;
    }

    /**
     * Accepts alphabet, space and numeric character
     */
    public function alphanumeric($value, $param, $field)
    {
        $value = is_array($value) ? reset($value) : $value;
        $value = trim($value);

        if (isset($param['spaces']) && $param['spaces']) {
            $pattern = '/^[ a-zA-Z0-9]+$/';
        } else {
            $pattern = '/^[a-zA-Z0-9]+$/';
        }

        return preg_match($pattern, $value);
    }

    /**
     * Accepts numeric only without the stated symbols
     */
    public function noSymbols($value, $param, $field)
    {
        $value = is_array($value) ? reset($value) : $value;

        $symbols = ['*', '$', '#', ':', '%', '\\', '/', '<', '>', ';', '&', '|', '='];

        $symbol = implode('\\', $symbols);
        $pattern = "/[$symbol]/";

        return !preg_match($pattern, $value);
    }

    /**
     * Accepts only numeric digits
     */
    public function numeric($value, $param, $field)
    {
        $value = is_array($value) ? reset($value) : $value;
        $value = trim($value);

        $pattern = '/^[0-9]+$/';

        return preg_match($pattern, $value);
    }

    /**
     * Accept numeric plus some special symbols
     */
    public function numericSymbols($value, $param, $field)
    {
        $value = is_array($value) ? reset($value) : $value;
        $value = trim($value);

        $pattern = '/^[0-9.+\\-()]+$/';

        return preg_match($pattern, $value);
    }

    /**
     * RFC email compliant
     */
    public function email($value, $param, $field)
    {
        $value = is_array($value) ? reset($value) : $value;

        $pattern = '/[A-Z0-9a-z._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,6}/';

        return preg_match($pattern, $value);
    }

    /**
     * Only accept specified minimum length
     */
    public function min($value, $param, $field)
    {
        $min = (integer) $param[0];

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return strlen($value) >= $min;
    }

    /**
     * Only accept specified maximum length
     */
    public function max($value, $param, $field)
    {
        $max = (integer) $param[0];

        if (is_array($value)) {
            return count($value) <= $max;
        }

        return strlen($value) <= $max;
    }

    /**
     * Accepts alphabet and space (optional) character
     */
    public function alpha($value, $param, $field)
    {
        $value = is_array($value) ? reset($value) : $value;
        $value = trim($value);

        if (isset($param['spaces']) && $param['spaces']) {
            $pattern = '/^[ a-zA-Z]+$/';
        } else {
            $pattern = '/^[a-zA-Z]+$/';
        }

        return preg_match($pattern, $value);
    }

    /**
     * Accepts alphabet, space, numeric and allow/disallow characters
     */
    public function alphaMulti($value, $param, $field)
    {
        static $unicode;

        if (! isset($unicode)) {
            $ymlRegex = Yaml::parse(file_get_contents(APP_ROOT . "/core/app/config/regex.yml"));
            $unicode = $ymlRegex['multi_lang_pattern'];
        }

        $pattern = '';
        $specialChars = '';
        // check if param is defined
        if (isset($param)) {
            $specialChars = $param['special'];
            // if spaces are allowed, change the regex to allow spaces
            if (array_key_exists('space', $param) && $param['space']) {
                $pattern = $pattern . ' ';
            }
            // check if we need to accept numeric
            if (array_key_exists('numeric', $param) && $param['numeric']) {
                $pattern = $pattern . '0-9';
            }
            // check if we need to allow any special characters
            if (( array_key_exists('allow', $param) && $param['allow'] ) &&
                ( array_key_exists('disallow', $param) && !$param['disallow'] ) ) {
                $pattern = $pattern . '' . $specialChars;
            }
        }
        // check if we need to disallow any special characters
        if (array_key_exists('disallow', $param) && $param['disallow']) {
            if (preg_match('/^[' . $specialChars . ']/', $value)) {
                return false;
            }
            return true;
        }

        $pattern = $pattern . $unicode;

        if (preg_match('/^[' . $pattern . ']+$/u', $value)) {
            return true;
        }

        return false;
    }

    public function regex($value, $param, $field)
    {
        return preg_match('/' . $param[0] . '/', $value) ? true : false;
    }


    public function validDate($value, $param, $field)
    {
        throw new \Exception("Error Processing Request");
    }

    /**
     * Validation for number required in input field
     */
    public function requiredNumberValue()
    {
        return true;
    }

    /**
     * Validation for at least one capital letter in input field
     */
    public function requireCapitalLetterValue()
    {
        return true;
    }

    /**
     * Validation for at least one lower letter in input field
     */
    public function requireLowerLetterValue()
    {
        return true;
    }

    public function invalidWords()
    {
        return true;
    }
}
