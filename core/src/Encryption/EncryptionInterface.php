<?php

namespace App\Encryption;

interface EncryptionInterface
{
    /**
     * Encrypts a payload
     *
     * @param string $payload The payload to encrypt
     * @param array|string $options Additional options to pass to the encryption library
     *
     * @return string
     */
    public function encrypt($payload, array $options);

    /**
     * Decrypts an encrypted string
     *
     * @param string $string The string to decrypt
     * @param string $options Additional options to pass to the encryption library
     *
     * @return string|array
     */
    public function decrypt($string, array $options);
}
