# Adelement Integration

> Make sure that the Market Scripts Drupal module is enabled before proceeding with this guide

Adelement is a custom Market scripts, but requires custom data to make Adelement work.

To fully integrate Adelement, it requires the current player's hash username
as a parameter.

## Enable the Provider Plugin

The Adelement front end integration uses the `Javascript Provider` plugins to expose
the needed data.

To activate this, define the following on `app/config/scripts.yml` of your product site

```yaml
providers:
  marketing_scripts: App\Javascript\Providers\MarketScripts
  adelement: App\Javascript\Providers\Adelement
```

That should expose several variables that can be used when creating a marketing script:

* `app.settings.marketing_scripts.username` - The raw player username
* `app.settings.marketing_scripts.adelement.username` - The encypted username
* `app.settings.marketing_scripts.adelement.depth` - The depth configuration value
* `app.settings.marketing_scripts.adelement.page` - The page configuration value

Content editors can just construct the script using the Marketing script module,
and use these values whenever they want.
