# Getting Started with Web Composer

A short outline on how to get started with Web Composer

### Important terms to remember

* **Front End Layer**
    * Front facing layer available publicly
    * Based on Slim but is a custom framework altogether
    * Connects to API layer to fetch data
    * Contains product specific business logic

* **FE Core**
    * Resuable sets of standardized components used by the Front End Layer
    * Provides framework capability for Front end product instances

* **API Layer**
    * Middleware layer that connects to Drupal and iCore
    * Not publicly accessible
    * Handles caching of responses from Drupal and iCore
    * Based on Symfony 3

* **Drupal Layer**
    * Provides CMS management capability
    * Provides content related data
    * Not publicly accessible

### Checking out the repositories

* Check out [API Layer](https://gitlab.ph.esl-asia.com/CMS/cms-api), [Drupal Layer](https://gitlab.ph.esl-asia.com/CMS/drupal-data), 
and [Front End Layer](https://gitlab.ph.esl-asia.com/CMS/demo) of a specific product
* Run `composer install` on each layer upon checkout

### Setting up virtual hosts

* API Layer 
    * **logic.local**
    * For Symfony, point your virtual host to `web` folder

* Drupal Layer
    * **demo.drupal.local**
    * Change **demo** to your product code
    * Product code can be seen on front ends `app/settings.php` under the `product` setting entry
    * Point your virtual host to `web` folder

* Front End Layer
    * **demo.dafabet.local**
    * Change **demo** to your product code
    * Point your virtual host to `web` folder

### Configuring Drupal

* You can either import a working DB or generate a DB from a the current configuration. Choose only one.

* **Importing a working DB**
    * Go to Drupal's `web/sites/demo/settings.php` (demo is your product code)
    * Configure the `database` settings entry
    * Visit the site then clear the cache

* **Generating DB from config**
    * Go to Drupal's `web/sites/demo/settings.php` (demo is your product code)
    * Delete the `database` settings entry
    * Visit the site, it will ask you to supply database details
    * When asked about the config directory, it should be populated already just click next

### Post configurations

* Front End Layer
    * On `core/core/app/parameters.php` your API layer endpoint might be different, adjust it accordingly, **but don't commit it**
    * Go to `devtool` folder then run `yarn install` then followed by `yarn run dev` to compile the assets

* API Layer
    * Go to `app/config/parameters.yml` and adjust your Drupal endpoint or iCore endpoint accordingly
    * Run clear cache by executing `bin/console cache:clear` on the root directory

* Drupal Layer
    * Clear the cache because Drupal that's why

### Final touches

* Visit your front end instance, if no errors found, congratulations.
