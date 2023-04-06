# Installation

## Requirements

* PHP 7.3+
* Composer 2.0+

## Install WP_Mock

Add WP_Mock as a dev-dependency using Composer:

```shell
composer require --dev 10up/wp:mock
```

## Dependencies

WP_Mock needs the following dependencies to work:

* PHPUnit ^9.5 (BSD 3-Clause license)
* Mockery ^1.5 (BSD 3-Clause license)
* Patchwork ^2.1 (MIT license)

They will be installed for you by Composer.

Next, you will need to configure PHPUnit first before enabling WP_Mock. [Consult PHPUnit documentation](https://phpunit.de/documentation.html) for this step.

You will also need to configure WP_Mock with a bootstrap file to use it in your tests.