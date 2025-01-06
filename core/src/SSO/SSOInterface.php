<?php

namespace App\SSO;

interface SSOInterface
{
    /**
     * The query name for the sso token
     */
    const QUERY = 'sso-token';

    /**
     * The cookie name for the sso cookie flag
     */
    const COOKIE = 'ssotimestamp';

    /**
     * Gets the SSO server URI
     *
     * @return string
     */
    public function getServerUri();

    /**
     * Check if this domain should be allowed to access the server
     *
     * @return boolean
     */
    public function isDomainAllowed($domain);

    /**
     * Gets the unique identifier of the server
     *
     * @return string
     */
    public function getServerIdentifier();

    /**
     * Defines wheter the client is already authenticated
     *
     * @param string $id The session ID being validated
     *
     * @return boolean
     */
    public function isClientAuthenticated($id);

    /**
     * Set the client's new identifier
     *
     * @param string $id The new session ID to use
     *
     * @return boolean
     */
    public function setClientIdentifier($id);
}
