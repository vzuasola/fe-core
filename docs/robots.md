# Activating the Robots Definition

> There is a Robots configuration for webcomposer_config module which you can
> configure the content of the Robots.txt

You just add this on `routes.yml`

```yaml
Robots.txt

/robots.txt:
    method: GET
    action: App\Controller\RobotsController:getRobotsConfig
```
