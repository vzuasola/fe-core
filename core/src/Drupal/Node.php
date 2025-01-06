<?php

namespace App\Drupal;

/**
 * Class for node related methods
 */
class Node
{
    /**
     * Player session
     *
     * @var object
     */
    private $playerSession;

    /**
     * Public constructor.
     */
    public function __construct($playerSession)
    {
        $this->playerSession = $playerSession;
    }

    /**
     * Filter nodes by boolean login state
     *
     * @param array $collection
     */
    public function filterByLoginState(&$collection)
    {
        $isLogin = $this->playerSession->isLogin();

        $collection = array_filter($collection, function ($value, $key) use ($isLogin) {
            return $this->hasAccess($value['field_log_in_state']);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Handles checking of login state for nodes
     *
     * 0 - Pre login
     * 1 - Post login
     * 2 - Both
     *
     * @param array Array of login states field
     *
     * @return boolean
     */
    public function hasAccess($states)
    {
        $isLogin = $this->playerSession->isLogin();

        foreach ($states as $state) {
            $value = (integer) $state['value'];

            switch (true) {
                case $value == 2:
                case $value == 0 && ! $isLogin:
                case $value == 1 && $isLogin:
                    return true;
            }
        }

        return false;
    }

    /**
     * Filter entity by PublishDate and UnpublishDate.
     *
     * @param array $collection
     */
    public function filterByPublishDate(&$collection)
    {
        $dateNow = time();

        $collection = array_filter($collection, function ($value) use ($dateNow) {
            if (empty($value['field_publish_date']) && empty($value['field_unpublish_date'])) {
                return true;
            }

            if (!empty($value['field_publish_date']) && empty($value['field_unpublish_date'])) {
                $date = strtotime($value['field_publish_date'][0]['value']);
                return $dateNow >= $date;
            }

            if (empty($value['field_publish_date']) && !empty($value['field_unpublish_date'])) {
                $date = strtotime($value['field_unpublish_date'][0]['value']);
                return $dateNow <= $date;
            }

            $publishDate = strtotime($value['field_publish_date'][0]['value']);
            $unpublishDate = strtotime($value['field_unpublish_date'][0]['value']);
            return $dateNow >= $publishDate && $dateNow <= $unpublishDate;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * This will convert the UTC based publish/unpublish date to the server Timezone
     *
     * @param array $collection
     */
    public function filterByPublishDateUTC(&$collection)
    {
        $dateTime = new \DateTime();
        $timestamp = $dateTime->format('U');
        $offset = $dateTime->getOffset();

        $collection = array_filter($collection, function ($value) use ($timestamp, $offset) {
            $result = true;
            if (!empty($value['field_publish_date']) && empty($value['field_unpublish_date'])) {
                $date = (strtotime($value['field_publish_date'][0]['value']) + $offset);
                $result = ($timestamp >= $date);
            } elseif (empty($value['field_publish_date']) && !empty($value['field_unpublish_date'])) {
                $date = (strtotime($value['field_unpublish_date'][0]['value']) + $offset);
                $result = ($timestamp <= $date);
            } elseif (!empty($value['field_publish_date']) && !empty($value['field_unpublish_date'])) {
                $publishDate = (strtotime($value['field_publish_date'][0]['value'])  + $offset);
                $unpublishDate = (strtotime($value['field_unpublish_date'][0]['value'])  + $offset);
                $result = ($timestamp >= $publishDate && $timestamp <= $unpublishDate);
            }

            return $result;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
