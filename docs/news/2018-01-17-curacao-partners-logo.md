# Curacao Partners Logo

Guide on how to enable Curacao's footer logo

> Make sure to pickup the Footer Partners Rework as it is a prerequisite
> of this feature

Curacao logo is dynamically created by inserting a Javascript onto the page
to create a logo at a specific position.

To add Curacao as a partner logo:

* Make sure to use the latest working branch for Drupal, API and fe-core, and rerun
yarn

* Configure Drupal

On Drupal, configure `Curacao Settings` (admin/config/webcomposer/config/curacao) and
append the following values:

Script URI:
```
https://e2e82a2c-05fe-4ad9-be2f-be3874730cd4.snippet.antillephone.com/apg-seal.js
```

Markup:
```
<div
    id="apg-seal-container"
    data-apg-seal-id="e2e82a2c-05fe-4ad9-be2f-be3874730cd4"
    data-apg-image-size="128"
    data-apg-image-type="basic-small"
>
</div>
```

* Configure FE

On `sections.yml` apply the alter as follows

```yaml
alters:
    footer_common: App\Extensions\Section\CuracaoAlter
```

Or if you are using Async

```yaml
alters:
    footer_async: App\Extensions\Section\CuracaoAlter
```

* Manage the Webcomposer Partners Entities

Just create a partner entity, upload a **logo** image, and specify the **ID** as
`curacao`, FE should pick this up and replace this accordingly.

Uploaded image is used as fallback for alternative domains.
