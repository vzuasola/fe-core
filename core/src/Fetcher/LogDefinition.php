<?php

namespace App\Fetcher;

use App\Fetcher\Integration\BalanceFetcher;
use App\Fetcher\Integration\ChangePasswordFetcher;
use App\Fetcher\Integration\SessionFetcher;
use App\Fetcher\Integration\UserFetcher;
use App\Fetcher\Drupal\BlockFetcher;
use App\Fetcher\Drupal\ConfigFetcher;
use App\Fetcher\Drupal\FormBuilderFetcher;
use App\Fetcher\Drupal\LanguageFetcher;
use App\Fetcher\Drupal\MenuFetcher;
use App\Fetcher\Drupal\NodeFetcher;
use App\Fetcher\Drupal\TaxonomyFetcher;
use App\Fetcher\Drupal\TerritoryBlockingFetcher;
use App\Fetcher\Drupal\ViewsFetcher;
use App\Fetcher\Integration\JIRAFetcher;

abstract class LogDefinition
{
    // Integration fetcher
    const FETCHERS = [
        BalanceFetcher::class => [
            'failed' => [
                'action' => 'Get player balance from Logic Layer',
                'object' => 'Balance retrieval',
                'status' => 'Failed to get player balance'
            ]
        ],
        ChangePasswordFetcher::class => [
            'failed' => [
                'action' => 'Change player password',
                'object' => 'Change password',
                'status' => 'Failed to change player password'
            ]
        ],
        SessionFetcher::class => [
            'failed' => [
                'action' => 'Authentication player session',
                'object' => 'Login, logout and session authentication',
                'status' => 'Failed session verification'
            ]
        ],
        UserFetcher::class => [
            'failed' => [
                'action' => 'Getting player details',
                'object' => 'Player details',
                'status' => 'Failed to get player details'
            ]
        ],
        LanguageFetcher::class => [
            'failed' => [
                'action' => 'Get languages',
                'object' => 'Drupal languages',
                'status' => 'Failed to get language'
            ]
        ],
        BlockFetcher::class => [
            'failed' => [
                'action' => 'Get blocks',
                'object' => 'Drupal blocks',
                'status' => 'Failed to get blocks'
            ]
        ],
        ConfigFetcher::class => [
            'failed' => [
                'action' => 'Get configurations',
                'object' => 'Drupal configurations',
                'status' => 'Failed to get configurations'
            ]
        ],
        FormBuilderFetcher::class => [
            'failed' => [
                'action' => 'Get configurations',
                'object' => 'Drupal configurations',
                'status' => 'Failed to get configurations'
            ]
        ],
        LanguageFetcher::class => [
            'failed' => [
                'action' => 'Get languages',
                'object' => 'Drupal languages',
                'status' => 'Failed to get language'
            ]
        ],
        MenuFetcher::class => [
            'failed' => [
                'action' => 'Get menus',
                'object' => 'Drupal menus',
                'status' => 'Failed to get menu'
            ]
        ],
        NodeFetcher::class => [
            'failed' => [
                'action' => 'Get nodes',
                'object' => 'Drupal nodes',
                'status' => 'Failed to get language'
            ]
        ],
        TaxonomyFetcher::class => [
            'failed' => [
                'action' => 'Get taxonomies',
                'object' => 'Drupal taxonomies',
                'status' => 'Failed to get taxonomies'
            ]
        ],
        TerritoryBlockingFetcher::class => [
            'failed' => [
                'action' => 'Get restricted countries',
                'object' => 'Drupal configurations',
                'status' => 'Failed to get restricted countries'
            ]
        ],
        ViewsFetcher::class => [
            'failed' => [
                'action' => 'Get views',
                'object' => 'Drupal views',
                'status' => 'Failed to get views'
            ]
        ],
        JIRAFetcher::class => [
            'failed' => [
                'action' => 'JIRA Integration',
                'object' => 'JIRA Ticket',
                'status' => 'Error while handling ticket'
            ]
        ],
    ];
}
