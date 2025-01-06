<?php

namespace App\Monolog;

/**
 *
 */
class Workflows
{
    /**
     * List of all available fields
     *
     * @var array
     */
    const FIELDS = [
        'ip',
        'timestamp',
        'phpsessid',
        'hostname',
        'url',
        'referrer',
        'workflow',
        'username',
        'product',
        'platform',
        'country_code',
        'session_guid',
        'language',
        'status_code',
        'request',
        'response',
        'game_code',
        'play_type',
        'game_url',
        'iapi_config',
        'others',
        'stack_trace',
    ];
}
