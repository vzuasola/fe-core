<?php

namespace App\Player;

/**
 * Defines how the application interacts with the player session
 */
interface PlayerSessionInterface
{
    /**
     * Checks if the player is in login state
     *
     * @return boolean
     */
    public function isLogin();

    /**
     * Gets the current active username
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get if player needs 2FA authentication
     *
     * @return string
     */
    public function getIsOTPRequired();

    /**
     * Authenticates a player
     *
     * @return boolean
     */
    public function login($username, $password);

    /**
     * Logout the current authenticated player
     *
     * @return boolean
     */
    public function logout();

    /**
     * Gets the current authentication token
     *
     * @return string
     */
    public function getToken();

    /**
     * Reauthenticates a user using a login credentials and an authentication
     * token
     *
     * @param string $username
     * @param string $password
     * @param string $token
     *
     * @return boolean
     */
    public function reauthenticateByToken($username, $password, $token);

    /**
     * Authenticates a user using an authentication token
     *
     * @param string $token
     *
     * @return boolean
     */
    public function authenticateByToken($token);

    /**
     * Check Session Password
     *
     * @param $username
     * @param $password
     *
     * @return boolean
     */
    public function validateSessionPassword($username, $password);
}
