# Update of Sponsor and Partner Entity Fields

The Sponsor and Partner entities does not have ALT support for image upload fields.
This change is required so that ALT text will be printed out on the front end.

* **Update Sponsors**
    * Go to `/admin/structure/paragraphs_type/sponsor_cmi/fields/paragraph.sponsor_cmi.field_sponsor_cmi_logo`
    * Check the `Enable ALT field` settings then save

* **Update Partners**
    * Go to `/admin/structure/partner_entity/settings/fields/partner_entity.partner_entity.field_logo`
    * Check the `Enable ALT field` settings then save

* Make sure to commit the latest configuration sync for your product site
