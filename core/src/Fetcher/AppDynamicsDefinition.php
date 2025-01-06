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

abstract class AppDynamicsDefinition
{
    const GUZZLE_EXCEPTION = [
        'description' => "Encountered a request failure on Frontend nodes to CMS-API.<br>",
        'troubleshooting' => "
            Please do the following troubleshooting:
            - Check application and server logs of Frontend nodes in Kibana or /var/log/cms for exceptions
        ",
    ];
}
