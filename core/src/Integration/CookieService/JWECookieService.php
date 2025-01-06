<?php

namespace App\Integration\CookieService;

use App\Integration\CookieService\LogTrait;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Easy\Build;
use Jose\Easy\Load;

/**
 * JWE Cookie Service
 */
class JWECookieService
{
    use LogTrait;

    /**
     * Dynamic monolog logger object
     *
     * @var object
     */
    private $logger;

    /**
     * Parameters Object
     */
    private $parameters;

    /**
     * Container resolver
     */
    public static function create($container)
    {
        return new static($container->get('logger'), $container->get('parameters'));
    }

    /**
     * Public constructor.
     *
     * @param object
     * @param object
     */
    public function __construct($logger, $parameters)
    {
        $this->logger = $logger;
        $this->parameters = $parameters;
    }

    /**
     * Generate JWE
     *
     * @param array $data
     *
     * @return string
     */
    public function cut($data)
    {
        try {
            $time = time();
            $issuer = $this->parameters['jwe.cookie.service.issuer'];
            $key = $this->parameters['jwe.cookie.service.key'];
            $alg = $this->parameters['jwe.cookie.service.alg'];
            $enc = $this->parameters['jwe.cookie.service.enc'];
            $subject = $this->parameters['jwe.cookie.service.subject'];
            $exp = $this->parameters['jwe.cookie.service.exp'];
            $alias = $this->parameters['jwe.cookie.service.alias'];

            $jwk = JWKFactory::createFromKeyFile($key);
            $jwe = Build::jwe() // We build a JWE
                ->exp($time + $exp)
                ->iat($time)
                ->nbf($time)
                ->iss($issuer)
                ->sub($subject)
                ->alg($alg)
                ->enc($enc)
                ->header('alias', $alias)
                ->claim('sso', $data)
                ->encrypt($jwk)
            ;

            return $jwe;
        } catch (\Exception $e) {
            $this->logException('Generic Error', null, $data, $e);

            throw $e;
        }
    }

    /**
     * Validates request body.
     *
     * @param array $data
     */
    public function validate($data)
    {
        if (isset($data['username']) && empty($data['username'])) {
            throw new \UnexpectedValueException('Empty username.');
        }

        if (isset($data['playerId']) && empty($data['playerId'])) {
            throw new \UnexpectedValueException('Empty playerId.');
        }

        if (isset($data['sessionToken']) && empty($data['sessionToken'])) {
            throw new \UnexpectedValueException('Empty sessionToken.');
        }
    }
}
