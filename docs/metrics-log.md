# Metrics Log

## Server Side Metrics Log

There is a static method call to invoke the metrics log

```php
\App\Kernel::logger('workflow')->info('AUTH.LOGIN', [
    'status_code' => 'NOT OK',
    'request' => [
        'username' => $username,
    ],
    'response' => [
        'message' => $e->getMessage(),
    ],
]);
```

You can pass an object to the log method that contains a workflow key.
The valid workflow keys you can pass can be found on `App\Monolog\Workflows`

## Client Side Metrics Log

The logger enables you to call the metrics logger thru an AJAX post request

> Make sure that Metrics Log is configured in Drupal before doing this step

```javascript
import Logger from "Base/logger";

Logger.log('AUTH.PAS', {
    status_code: 'OK',
    request: request,
    response: response,
    others: 'Other data I want to log',
});
```

You can pass an object to the log method that contains a workflow key.
The valid workflow keys you can pass can be found on `App\Monolog\Workflows`
