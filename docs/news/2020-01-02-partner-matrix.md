# Partner Matrix Redirection

Adding this line to **app/middlewares.yml** will redirect the agent players to dafa sports.
Remove this to disable the redirection.

```yml
response:
    ...
    partnermatrix: App\Middleware\Response\PartnerMatrixRedirection
    ...
```

Allowing access to the site with LATAM redirection rule by adding this line to **settings.php**
and retaining the middleware.

```php
$settings['settings']['partner_matrix']['latam_redirect'] = 'en/sports-df';
```