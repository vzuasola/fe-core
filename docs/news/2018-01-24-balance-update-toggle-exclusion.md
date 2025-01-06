# Balance Feature Update

The header balance components has been updated and will now have these two
additional features
* **State toggle**
* **Dynamic ignore list**
* **Product mapping per currency**

### Configuration Update

Drupal has been updated to contain the following additional configuration

* **Product Balance Label**
    * The label for the product balance text
* **Total Balance Label**
    * The label for the total balance text
* **Product Balance ID**
    * You specify here which product will be shown as the secondary balance
* **Currency Balance Mapping**
    * Specify here product IDs that will only show up on specified currencies indicated.
    Default value should be **7|RMB**
    * You can specify here product ID to currency pairs, example format would be
    **7|RMB,USD,KRW** one entry per line
* **Excluded Balance Mapping**
     * You specify here one wallet ID per line for product that will be excluded on first load.
     Please put **7** as the default value

### Migration Guide

You may need to visit your Drupal's balance configuration `admin/config/webcomposer/config/header` and supply
the default values.

* **Put `7` as the default value for the Excluded Balance Mapping**
* **Put `7|RMB` as the default value for Currency Balance Mapping**
