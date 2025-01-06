<?php

namespace App\Encryption;

use Lcobucci\JWT\Claim\Basic;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class JWTEncryption implements EncryptionInterface
{
    /**
     * The JWT token
     */
    const JWT_TOKEN = '2f99a4c96706451e500d774cd46d48fe';

    /**
     * @{inheritdoc}
     */
    public function encrypt($payload, array $options)
    {
        $builder = new Builder();
        $signer = new Sha256(); // crypto algo

        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                $builder->set($key, $value);
            }
        } else {
            $builder->set('payload', $payload);
        }

        // configures the issuer (iss claim)
        if (isset($options['issuer'])) {
            $builder->setIssuer($options['issuer']);
        }

        // configure the audience of your token
        if (isset($options['audience'])) {
            $builder->setAudience($options['audience']);
        }

        // configures the expiration time of the token (exp claim)
        if (isset($options['expire_time'])) {
            $builder->setExpiration($options['expire_time']);
        }

        // Creating objects of required classes
        return (string) $builder->sign($signer, self::JWT_TOKEN)
            ->getToken();
    }

    /**
     * @{inheritdoc}
     */
    public function decrypt($string, array $options)
    {
        $validate = new ValidationData();
        $parser = new Parser();
        $signer = new Sha256();

        // parse the encoded token
        $token = $parser->parse($string);

        // validate if the token is valid
        $validate->setIssuer($options['issuer']);
        $validate->setAudience($options['audience']);

        // verify if the token was not modified after its generation
        if ($token->verify($signer, self::JWT_TOKEN) && $token->validate($validate)) {
            return $this->getBasicClaims($token);
        }

        return false;
    }

    /**
     * Get only the basic claims
     *
     * If the payload is an array, it will return an array, if it is just a
     * string, will return string otherwise
     *
     * @return string|array
     */
    private function getBasicClaims($token)
    {
        $result = [];

        foreach ($token->getClaims() as $key => $value) {
            // only get the basic claims
            if (get_class($value) === Basic::class) {
                $result[$key] = $value->getValue();
            }
        }

        if (count($result) > 1) {
            return $result;
        }

        return reset($result);
    }
}
