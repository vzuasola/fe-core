# Rework of Footer Partners

The footer partners section (the one that contains CEZA etc) has now been reimplemented.

Previous implementation defines that the entire partners configuration consists
of one single image. The rework opt out for one image per logo entry.

### Migration Guide

* Enable the `webcomposer_partner` module
* Update your product site's `fe-core` reference
* Reupload partners logo on the new UI provided by the new module `(/admin/structure/partner_entity)`
