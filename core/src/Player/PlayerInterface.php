<?php

namespace App\Player;

/**
 * Defines what details a player should contain
 */
interface PlayerInterface
{
    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @return int
     */
    public function getPlayerID();

    /**
     * @return int
     */
    public function getProductID();

    /**
     * @return string
     */
    public function getVipLevel();

    /**
     * @return boolean
     */
    public function hasAccount($product);

    /**
     * Player Details
     *
     */

    /**
     * @return string
     */
    public function getCountryCode();

    /**
     * @return int
     */
    public function getCountryID();

    /**
     * @return string
     */
    public function getCountryName();

    /**
     * @return string
     */
    public function getGender();

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @return string[]
     */
    public function getMobileNumbers();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return int
     */
    public function getBirthDate();

    /**
     * @return int
     */
    public function getIsPlayerCreatedByAgent();
}
