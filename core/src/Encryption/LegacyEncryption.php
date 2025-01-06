<?php

namespace App\Encryption;

/**
 * Class that handles legacy encryption
 */
class LegacyEncryption implements EncryptionInterface
{
    /**
     * The web composer nonce
     */
    const NONCE = '27a1b0195b2d9db38f560872b44c74ea';

    /**
     * {@inheritdoc}
     */
    public function encrypt($str, array $options)
    {
        $key = $options['key'] ?? self::NONCE;

        // Generate the random length to be used.
        $length = mt_rand(16, 32);
        $nonce = $this->generateRandomString($length);

        // Using the size generate the initialization vector
        $iv = openssl_random_pseudo_bytes(16);

        // Generate cipher
        $cipherText  = openssl_encrypt($str, 'AES256', $key, 0, $iv);

        // Include the iv the the size so we can decrypt it later
        return $this->safeBase64Encode($iv . $cipherText . $nonce . $length);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($str, array $options)
    {
        $key = $options['key'] ?? self::NONCE;

        try {
            $str = $this->safeBase64Decode($str);

            // Retrieve the size of the nonce
            $size = (int) substr($str, -2, 2);

            // Retrieve the iv
            $iv = substr($str, 0, 16);

            // Get the ciphertext from the encrypted message
            $cipherText = substr(substr($str, 16), 0, -($size + 2));

            $encryptText = openssl_decrypt($cipherText, 'AES256', $key, 0, $iv);

            return openssl_decrypt($cipherText, 'AES256', $key, 0, $iv);
        } catch (\Exception $e) {
            // return NULL
        }
    }

    /**
     * Helper function for safe base64 encoding
     */
    public static function safeBase64Encode($str)
    {
        return strtr(base64_encode($str), '+/=', '-_.');
    }

    /**
     * Helper function for safe base64 decoding
     */
    public static function safeBase64Decode($str)
    {
        return base64_decode(strtr($str, '-_.', '+/='));
    }

    /**
     * Helper function to generate random strings
     */
    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
