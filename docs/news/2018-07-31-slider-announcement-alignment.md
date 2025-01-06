# Slider and Announcement Alignment

The slider and announcment components needs to be aligned in order to play nicely with page caching (and fetcher caching).

### What was change

Slider and announcement now filters scheduled content via the client side instead of having Drupal do it

### What needs to be done

Product teams needs to align their implementation to support this change

<br>

## Required Changes for Slider

### Front End Changes

> Since slider implementation differs from product to product, adjust your implementation accordingly

You need to re initialize a new banner object, and create a handlebar banner template along with it (you need to deprecate your slider twig template)

```javascript
import Banner from 'Base/banner/banner';
import BannerTemplate from "SiteTemplate/handlebars/banner/banner.handlebars";

new Banner({
    template: BannerTemplate,
    sliderOpts: {
        selector: '#main-banner-section',
        innerSelector: '.banner-slides',
        childClassSelector: 'banner-slides-item',
        auto: true,
        controls: true,
        pager: true,
        speed: 4000
    }
});
```

Refer to the implementation done by Product 1 team for actual reference https://gitlab.ph.esl-asia.com/CMS/casino/merge_requests/648/diffs

### Drupal Changes

On your **slider views**, remove the filter done for the scheduled dates

<br>

## Required Changes for Announcement

### Front End Changes

Just change your import for announcement from the old class, to the new class

```javascript
import announcements from "Base/announcements/announcements";
```

Refer to the implementation done by Product 1 here for actual reference https://gitlab.ph.esl-asia.com/CMS/casino/merge_requests/649/diffs

### Drupal Changes

On your **announcement views**, remove the filter done for the scheduled dates

<br>

## Activate Caching

After migrating slider and announcments, you can now happily enable performance caching and enjoy faster responses

```php
// Page Cache

$settings['settings']['page_cache']['enable'] = true;
$settings['settings']['page_cache']['default_timeout'] = 3600;

// Fetchers

$settings['settings']['fetchers']['enable_permanent_caching'] = true;
```

