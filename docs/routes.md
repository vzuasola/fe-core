# Routes Definition

The routes file defines an application's available path. It is defined on a YAML
schema file.

```yaml
routes:
    /:
        method: GET
        action: App\Product\Controller\PageController:home
        page_cache:
            enabled: true
            expires: 500
        headers:
            Custom-Header: my custom value
```

<br>
# Per Route Page Caching

You can enable page cache and specify different expiration time for each route

> This overrides the cache headers defined by the page cache

```yaml
routes:
    /some-route:
        page_cache:
            enabled: true
            expires: 500
```

<br>
# Defining Headers

Headers can be defined by specifying a key value pair on the headers schema of
the route

```yaml
routes:
    /some-route:
        headers:
            Custom-Header: my custom value
            Another-Header: my another value
```
