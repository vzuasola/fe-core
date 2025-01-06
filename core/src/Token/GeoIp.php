<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 *
 */
class GeoIp implements TokenInterface
{
    /**
     * Header name of geoip
     */
    const GEOIP_HEADER = 'x-custom-lb-geoip-country';

    /**
     * Request object
     *
     * @var object
     */
    private $request;

    private $logger;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->request = $container->get('router_request');
        $this->logger = $container->get('logger');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        [$geoCountryCode, $cdn] = $this->getGeoIpCountryData();
        if (!$this->isValidCountryCode($geoCountryCode)) {
            $msg = 'CountryCode: <' . $geoCountryCode . '> - CDN: <' . $cdn . '>';
            $this->logger->error('GeoIpCountryCodeWarning - ' . $msg);
            $geoCountryCode = 'XX';
        }

        return $geoCountryCode;
    }

    private function getGeoIpCountryData()
    {
        try {
            $geoCountryCode = $this->request->getHeaderLine('x-custom-lb-geoip-country');
        } catch (\Exception $ex) {
            $geoCountryCode = '';
        }

        try {
            $cdn = $this->request->getHeaderLine('x-custom-lb-cdn');
        } catch (\Exception $ex) {
            $cdn = '';
        }

        return [$geoCountryCode, $cdn];
    }

    // Same country code validation as the one in Registration API
    private function isValidCountryCode($code)
    {
        $codeLength = strlen($code);
        return ($codeLength >= 2 && $codeLength <= 50);
    }
}
