# Enable CDN Support

FE comes with CDN support. To enable CDN support, just do the following:

* Enable the **webcomposer_cdn** module

The CDN module only allows CDN targets by geolocation. When configuring the CDN
module, you need to specify a key value pair of country code and then the CDN
value. The mapping can be configured on `/admin/config/cdn_settings/cdn_config`

Example values could be `PH|http://ph.cdn.com/`

You can also use wildcard pattern matching like this

```
PH|http://ph.cdn.com/
*|http://cdn.com/
```

Which will define a different CDN for PH, but will use the other for everything else

* Make sure to disable **views cache**

Depending on your product, turn off views cache on most of your views. Views cache
interferes with CDN mapping.
