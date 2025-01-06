<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class AjaxAnnouncementsController extends BaseController
{
    /**
     * Fetches the Data for the published Announcement Content from Drupal.
     */
    public function getAnnouncements($request, $response)
    {
        $announcements = [];

        try {
            $contents = $this->get('views_fetcher')->getViewById('announcements');

            foreach ($contents as $item) {
                $announcements[] = [
                    'nid' => $item['id'][0]['value'],
                    'text' => $item['field_body'][0]['value'],
                ];
            }
        } catch (\Exception $e) {
            // do nothing
        }

        $data['data'] = $announcements;

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Fetches announcements filtered by publish/unpublish dates
     */
    public function getFilteredAnnouncements($request, $response)
    {
        try {
            $announcements = [];
            $contents = $this->get('views_fetcher')->getViewById('announcements');
            $nodeUtils = $this->get('node_utils');

            // Get the next announcement timer
            $nextAnnouncement = $this->getNextAnnouncementTimer($contents);

            // Filter contents base from publish dates
            $nodeUtils->filterByPublishDateUTC($contents);

            foreach ($contents as $item) {
                $announcements[] = [
                    'nid' => $item['id'][0]['value'],
                    'text' => $item['field_body'][0]['value'],
                ];
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return $this->get('rest')->output($response, [
            'data' => $announcements,
            'timer' => $nextAnnouncement
        ]);
    }

    /**
     * Get the next closest announcement to be publish
     */
    private function getNextAnnouncementTimer($announcements = [])
    {
        $dateTime = new \DateTime();
        $timestamp = $dateTime->format('U');
        $offset = $dateTime->getOffset();
        $nextAnnouncement = 0;

        foreach ($announcements as $announcement) {
            if (empty($announcement['field_publish_date'])) {
                continue;
            }

            $publishDate = (strtotime($announcement['field_publish_date'][0]['value']) + $offset);
            if ($publishDate > $timestamp && ($publishDate < $nextAnnouncement || 0 === $nextAnnouncement)) {
                $nextAnnouncement = $publishDate;
            }
        }

        // Get the time difference between the current time and the next announcement
        // Add an offset of 5 seconds to make sure to fetch the next one as well
        return $nextAnnouncement ? abs($nextAnnouncement - $timestamp) + 5:null;
    }
}
