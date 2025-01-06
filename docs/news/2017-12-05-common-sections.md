# Common Sections

Common Sections are special kind of sections that fetches data from the new common API.
This API serves all commonly used data in one endpoint, reducing the number of FE
Calls.

There are a handful of common sections defined on core's `section.yml`

```yaml
  # Aggregated Sections
  header_common: App\Section\Common\Header
  footer_common: App\Section\Common\Footer
  session_timeout_common: App\Section\Common\Session
  livechat_common: App\Section\Common\Livechat
  metatags_common: App\Section\Common\Metatags
  floating_banner_common: App\Section\Common\FloatingBanner
  legacy_browser_common: App\Section\Common\LegacyBrowser
  announcement_lightbox_common: App\Section\Common\AnnouncementLightbox
```

This section utilizes the Async Section definition and are designed to only call
one API even when multiple common sections are used.

To use the common sections, simply call it on your controllers instead of using
the Async or non Async equivalents.

```php
// Aggregate
$data['header'] = $this->getSection('header_common');
$data['footer'] = $this->getSection('footer_common');
$data['session'] = $this->getSection('session_timeout_common');
$data['livechat'] = $this->getSection('livechat_common');
$data['metatags'] = $this->getSection('metatags_common');
$data['floating_banner'] = $this->getSection('floating_banner_common');
$data['outdated_browser'] = $this->getSection('legacy_browser_common');
$data['announcement_lightbox'] = $this->getSection('announcement_lightbox_common');

// Async definitions
// $data['header'] = $this->getSection('header_async');
// $data['metatags'] = $this->getSection('metatags_async');
// $data['footer'] = $this->getSection('footer_async');
// $data['session'] = $this->getSection('session_timeout_async');
// $data['livechat'] = $this->getSection('livechat');
// $data['floating_banner'] = $this->getSection('floating_banner_async');
// $data['outdated_browser'] = $this->getSection('legacy_browser_async');
// $data['announcement_lightbox'] = $this->getSection('announcement_lightbox_async');
```
