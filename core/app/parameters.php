<?php

// Parameter configurations

// Logic Layer Integration

$parameters['logic.api.url'] = '%env(LOGIC_API_URL)%';
$parameters['env(LOGIC_API_URL)'] = 'http://logic.local';

$parameters['logic.api.drupal.prefix'] = '/api/v1/drupal';
$parameters['logic.api.integration.prefix'] = '/api/v1/integration';
$parameters['logic.api.game_provider.prefix'] = '/api/v1/game_providers';
$parameters['logic.api.jackpot_provider.prefix'] = '/api/v1/jackpot_provider';
$parameters['logic.api.user_data.prefix'] = '/api/v1/user';

// deprecated

$parameters['logic.api.jackpot.prefix'] = '/api/v1/jackpot';

// Redis
//
// This is currently not in used, but we will migrate to this approach in the
// future. Currently, Redis variable are dynamically fetched on settings.php

$parameters['redis.server'] = '%env(REDIS_SERVER)%';
$parameters['env(REDIS_SERVER)'] = '127.0.0.1';

$parameters['redis.service'] = '%env(REDIS_SERVICE)%';
$parameters['env(REDIS_SERVICE)'] = 'pushnx';

// Pushnx Service

$parameters['pushnx.api.eventbus.prefix'] = '/eventbus';
$parameters['pushnx.fallback.prefix'] = '/pushnx';
$parameters['pushnx.api.reply.prefix'] = '/api/v1/reply';
$parameters['pushnx.server'] = '%env(PUSHNX_SERVER)%';
$parameters['env(PUSHNX_SERVER)'] = 'https://pnxtct.chodeetsu.com';

// Application Service
$parameters['appsvc.origin.dev'] = 'http://ms.appsvc.dev';
$parameters['appsvc.origin.tct'] = 'http://ctmt-cms-appsvc.games.de1';
$parameters['appsvc.origin.itcto'] = 'http://cms-appsvc.games.itcto';
$parameters['appsvc.origin.qa1'] = 'http://cms-ms.appsvc.dev';
$parameters['appsvc.origin.qa2'] = 'http://ctmt-cms-appsvc.games.de1';
$parameters['appsvc.origin.uat'] = 'http://ctmu-cms-ms.appsvc.stg';
$parameters['appsvc.origin.stg'] = 'http://cms-ms.appsvc.stg';
$parameters['appsvc.origin.stg3'] = 'http://cms-ms.appsvc.stg';
$parameters['appsvc.origin.prd'] = 'http://cms-ms-appsvc.games.twprd';

// Application Service - IPVG
$parameters['appsvc.origin.itct'] = 'http://cms-appsvc.games.itct';
$parameters['appsvc.origin.its1'] = 'http://cms-appsvc.games.its1';
$parameters['appsvc.origin.iuat'] = 'http://cms-appsvc.games.iuat';
$parameters['appsvc.origin.istg'] = 'http://cms-appsvc.games.istg';

// JWE Cookie Service
$parameters['jwe.cookie.service.issuer'] = 'https://dafabet.com';
$parameters['jwe.cookie.service.key'] = __DIR__ . '/../keys/key.pem';
$parameters['jwe.cookie.service.alg'] = 'RSA-OAEP';
$parameters['jwe.cookie.service.enc'] = 'A256GCM';
$parameters['jwe.cookie.service.subject'] = 'dafabet';
$parameters['jwe.cookie.service.exp'] = 3600;
$parameters['jwe.cookie.service.alias'] = 'dafabet.com';

// Replace Google Service Account key with valid credentials
$parameters['google.service.key'] = BASE_ROOT . 'app/credentials/gdrive.json';
// JIRA Integration
$parameters['jira.user'] = '%env(JIRA_API_USER)%';
$parameters['env(JIRA_API_USER)'] = '';

$parameters['jira.apikey'] = '%env(JIRA_API_KEY)%';
$parameters['env(JIRA_API_KEY)'] = '';
