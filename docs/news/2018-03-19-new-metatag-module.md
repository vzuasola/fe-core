# New Metatag Module

The **webcomposer_seo** will contain the metatag implementation and will deprecate
the old **webcomposer_metatags** module. The new implementation will allow
definition of dynamic meta attributes, rather than having a fixed set of fields.

> As of this FE Core version, the metatag templates will only be compatible with
> the new module, migration is required

### Migration Guide

* Uninstall **webcomposer_metatags** module
* Enable **webcomposer_seo** module
* Management will be available on **/admin/structure/webcomposer_meta_entity**
