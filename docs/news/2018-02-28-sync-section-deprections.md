## Deprecation of Sync Sections

These sections were removed from the code base

```yaml
header: App\Section\Header
footer: App\Section\Footer
session_timeout: App\Section\SessionTimeout
announcement: App\Section\Announcement
announcement_lightbox: App\Section\AnnouncementLightbox
floating_banner: App\Section\FloatingBanner
inner_right_side: App\Section\InnerPageRight
legacy_browser: App\Section\LegacyBrowser
metatags: App\Section\Metatags
canonicals: App\Section\Canonicals
livechat: App\Section\LiveChat
downloadable: App\Section\Downloadable
sitemap: App\Section\Sitemap
```

It is recommended to use the asynchronous or common counterpart of these sections.
