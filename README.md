****
    Copyright © 2024 Mirakl. www.mirakl.com - info@mirakl.com
    All Rights Reserved. Tous droits réservés.
    Strictly Confidential, this data may not be reproduced or redistributed.
    Use of this data is pursuant to a license agreement with Mirakl.
****

# Mirakl Magento 2 Connector

This is the official Mirakl extension for Magento 2.

## How to use

### Prerequisites

 * PHP 8.1+
 ```bash
sudo apt-get install php8.1 php8.1-mcrypt php8.1-curl php8.1-cli php8.1-mysql php8.1-gd libapache2-mod-php8.1 php8.1-intl php8.1-zip php-xml php-mbstring
 ```

### Compatibility

 * Magento Open Source 2.4.4+
 * Adobe Commerce 2.4.4+

### Installation Steps

#### Install Magento 2

Create the database

`mysql -e "CREATE DATABASE magento2; GRANT ALL PRIVILEGES ON magento2.* TO magento2@localhost IDENTIFIED BY 'magento2'; flush privileges;"`

**Retrieve Magento 2 files**

```bash
cd path/to/magento2
composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition .
```

**Install Magento 2**

```bash
php bin/magento setup:install --base-url=http://local.url.mirakl.net/ \
  --db-host=localhost --db-name=magento2 --backend-frontname=admin \
  --db-user=magento2 --db-password=magento2 \
  --admin-firstname=Firstname --admin-lastname=Lastname --admin-email=email@mirakl.com \
  --admin-user=mirakl --admin-password=mirakl123 --language=en_US \
  --currency=USD --timezone=America/Chicago --cleanup-database \
  --sales-order-increment-prefix="MIR$" --session-save=files --use-rewrites=1
```
**Install Magento sample data**

```bash
php bin/magento sampledata:deploy
```

**Install French translation**

Append the following requirement in `composer.json` file:

```json
{
    "require": {
        "lalbert/magento2-fr_fr": "*"
    }
}
```

And execute the commands below:

```bash
composer update
php bin/magento module:enable --all
php bin/magento setup:upgrade
```

#### Install the Magento Connector

**With Satis**

Edit `composer.json` file and add:

```json
{
    "require": {
        "mirakl/connector-magento2-plugin": "*"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://satis.mirakl.net"
        },
        {
            "type": "composer",
            "url": "https://sdk-front-satis.mirakl.net/"
        }
    ]
}
```

And execute the commands below:

```bash
composer update
php bin/magento module:enable --all
php bin/magento setup:upgrade
```

**With GitHub (for Mirakl developer)**

Edit `composer.json` file and add:

```json
{
    "require": {
        "mirakl/mirakl-sdk-php": "*",
        "mirakl/connector-magento2-plugin": "dev-develop"
    },
    "minimum-stability": "dev",
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:mirakl/connector-magento2-plugin.git",
            "branch": "develop"
        },
        {
            "type": "vcs",
            "url": "git@github.com:mirakl/sdk-php.git",
            "branch": "develop"
        }
    ]
}
```

And execute the commands below:

```bash
composer update
php bin/magento module:enable --all
php bin/magento setup:upgrade
```

#### Configure Magento

After clearing the Magento cache, go to Admin Panel. A new Mirakl tab should appear in the navigation menu.

You can now start configuring Mirakl parameters and synchronize shops, offer states, shipping zones etc.
