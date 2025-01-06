<?php

namespace App\Utils;

/**
 * Class for handling IP related functionalities
 */
class IP
{
    /**
     * The reverse proxy keys (in specific order)
     */
    const REVERSE_PROXY_KEYS = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED_IP',
        'HTTP_TRUE_CLIENT_IP',
        'HTTP_INCAP_CLIENT_IP',
        'HTTP_CF_CONNECTING_IP',
    ];

    /**
     * THe default fallback IP
     */
    const DEFAULT_IP = '127.0.0.1';

    /**
     * Gets the IP address of the server
     *
     * @return string
     */
    public static function getIpAddress()
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? self::DEFAULT_IP;

        $proxyHeader = self::getReverseProxy();

        if (!empty($_SERVER[$proxyHeader])) {
            // If an array of known reverse proxy IPs is provided, then trust
            // the XFF header if request really comes from one of them.
            $proxyAddresses = [ $_SERVER['REMOTE_ADDR'] ];
            $forwarded = explode(',', $_SERVER[$proxyHeader]);

            // Trim the forwarded IPs; they may have been delimited by commas
            // and spaces.
            $forwarded = array_map('trim', $forwarded);

            // Tack direct client IP onto end of forwarded array.
            $forwarded[] = $ipAddress;

            // Eliminate all trusted IPs.
            $untrusted = array_diff($forwarded, $proxyAddresses);

            if (!empty($untrusted)) {
                // The right-most IP is the most specific we can trust.
                $ipAddress = array_pop($untrusted);
            } else {
                // All IP addresses in the forwarded array are configured proxy
                // IPs (and thus trusted). We take the leftmost IP.
                $ipAddress = array_shift($forwarded);
            }
        }

        return filter_var($ipAddress, FILTER_VALIDATE_IP) ? $ipAddress : self::DEFAULT_IP;
    }

    /**
     * Gets the reverse proxy header
     */
    private static function getReverseProxy()
    {
        foreach (self::REVERSE_PROXY_KEYS as $key) {
            if (!empty($_SERVER[$key])) {
                return $key;
            }
        }
    }
}
