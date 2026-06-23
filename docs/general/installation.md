# Installation

## Requirements

* PHP 7.4+
* Composer 2.0+

## Install WP_Mock

Add WP_Mock as a dev-dependency using Composer:

```shell
composer require --dev 10up/wp_mock
```

## Dependencies

WP_Mock needs the following dependencies to work:

* PHPUnit ^9.6 || ^10 || ^11 || ^12 || ^13 (BSD 3-Clause license)
* Mockery ^1.6.12 (BSD 3-Clause license)
* Patchwork ^2.1 (MIT license)

They will be installed for you by Composer. WP_Mock supports PHPUnit 9 through 13 from a single codebase; Composer installs the highest PHPUnit version your PHP version allows (for example PHP 7.4 resolves PHPUnit 9, while PHP 8.4 resolves PHPUnit 13).

Next, you will need to configure PHPUnit first before enabling WP_Mock. [Consult PHPUnit documentation](https://phpunit.de/documentation.html) for this step.

You will also need to [configure WP_Mock with a bootstrap file](configuration.md) to use it in your tests.