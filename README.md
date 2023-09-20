<p align="center">
    <img src="https://www.pay.nl/uploads/1/brands/main_logo.png" />
</p>
<h1 align="center">Hyvä Checkout support for PAY. Magento2 plugin</h1>

This plugin adds support for the PAY. payment methods to the Hyvä Checkout plugin. It requires the base PAY. Magento2 plugin as well as the Hyvä checkout plugin.

For details on the base functionality of PAY. refer to https://github.com/paynl/magento2-plugin

## Index
- [Requirements](#requirements)
- [Installation](#installation)
- [Update instructions](#update-instructions)
- [Support](#support)

# Requirements

The extension has been tested on a Magento environment with

- PHP 8.1
- Magento 2.4.6
- PAY. 3.5.2
- Hyvä Themes 1.2.3
- Hyvä Checkout 1.1.0

# Installation
In command line, navigate to the installation directory of Magento2

Enter the following commands:

```
composer require paynl/magento2-hyva-checkout
php bin/magento setup:upgrade
php bin/magento cache:clean
```

The plugin is now installed.

Make sure to also generate the tailwind css files for your theme.

If you are using an older version of Hyvä themes, it might be necessary to also run

```
php bin/magento hyva:config:generate
```

to make sure that the templates of this plugin are added to the tailwind purge configuration for styling.

# Update instructions
In command line, navigate to the installation directory of Magento2

Enter the following commands:

```
composer update paynl/magento2-hyva-checkout
php bin/magento setup:upgrade
php bin/magento cache:clean
```

The plugin has now been updated.

In some cases it can be necessary to update the base PAY. module before this Hyvä integration can be updated.
In that case you also have to run

```
composer update paynl/magento2-plugin paynl/sdk
php bin/magento setup:upgrade
php bin/magento cache:clean
```

# Usage

**More information on this plugin can be found on https://docs.pay.nl/plugins#magento-2**

# Support
https://www.pay.nl

Contact us: support@pay.nl