<?php

namespace App\Controller;

use App\Async\Async;

trait BaseSectionTrait
{
    /**
     * Get section that has no standard sections
     */
    protected function getAsyncData($data = [])
    {
        return Async::resolve($data);
    }

    /**
     * Get the most common and standard sections of the page
     */
    public function getBaseData()
    {
        return [
            'header' => $this->getSection('header_common'),
            'footer' => $this->getSection('footer_common'),
            'session' => $this->getSection('session_timeout_common'),
            'metatags' => $this->getSection('metatags_common'),
            'outdated_browser' => $this->getSection('legacy_browser_common'),
            'announcement_lightbox' => $this->getSection('announcement_lightbox_common'),
            'livechat' => $this->getSection('livechat_common'),
            'downloadable' => $this->getSection('downloadable_async'),
        ];
    }

    /**
     * Get common sections.
     */
    protected function pageSections($data = [])
    {
        return Async::resolve(array_replace($data, [
            'header' => $this->getSection('header_common'),
            'footer' => $this->getSection('footer_common'),
            'session' => $this->getSection('session_timeout_common'),
            'metatags' => $this->getSection('metatags_common'),
            'outdated_browser' => $this->getSection('legacy_browser_common'),
            'announcement_lightbox' => $this->getSection('announcement_lightbox_common'),
            'livechat' => $this->getSection('livechat_common'),
            'downloadable' => $this->getSection('downloadable_async'),
        ]));
    }
}
