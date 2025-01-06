<?php

namespace App\Session;

/**
 *
 */
class SecureSession
{
    const SALT = 'leandrew';

    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     *
     */
    public function get($index)
    {
        $key = hash('sha256', self::SALT);
        $iv = substr(hash('sha256', self::SALT), 0, 16);

        $string = $this->session->get($index);

        if (is_string($string)) {
            $result = openssl_decrypt(base64_decode($string), "AES-256-CBC", $key, 0, $iv);

            return unserialize($result);
        }
    }

    /**
     *
     */
    public function set($index, $value)
    {
        $value = serialize($value);

        $key = hash('sha256', self::SALT);
        $iv = substr(hash('sha256', self::SALT), 0, 16);

        $output = openssl_encrypt($value, "AES-256-CBC", $key, 0, $iv);
        $string = base64_encode($output);

        return $this->session->set($index, $string);
    }

    /**
     *
     */
    public function delete($index)
    {
        $this->session->delete($index);
    }
}
