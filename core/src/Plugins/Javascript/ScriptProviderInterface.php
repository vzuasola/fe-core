<?php

namespace App\Plugins\Javascript;

/**
 *
 */
interface ScriptProviderInterface
{
    /**
     * Define attachments
     *
     * @return array
     */
    public function getAttachments();
}
