# Configuration

After installing WP_Mock you will need to perform some simple configuration steps before you can start using it in your tests.

## Bootstrap WP_Mock

Before you can start using WP_Mock to test your code, you'll need to bootstrap the library by creating a bootstrap.php file.

Here is an example of a bootstrap you might use:

```php
<?php

// First we need to load the composer autoloader, so we can use WP Mock
require_once dirname(__DIR__).'/vendor/autoload.php';

// Bootstrap WP_Mock to initialize built-in features
WP_Mock::bootstrap();

// Optional step
// If your project does not use autoloading via Composer, include your files now
require_once dirname(__DIR__).'/my-plugin.php';
```

The bootstrap file can do a few things:

* Bootstraps the main WP_Mock handler which defines action and filter functions, as well as common WordPress constants
* Sets up Patchwork if it has been turned on (see below)
* Includes any custom files you need to run the test (optional)

## Configure PHPUnit with WP_Mock

You can run PHPUnit using a `--bootstrap` flag to include your boostrap configuration while executing your tests (see [PHPUnit documentation](https://docs.phpunit.de/en/9.5/textui.html?highlight=--bootstrap#command-line-options):

```shell
./vendor/bin/phpunit --bootstrap /path/to/bootstrap.php
```

A more convenient way though would be to add the following to the phpunit.xml configuration file (see [PHPUnit documentation](https://docs.phpunit.de/en/9.5/configuration.html)):

```shell
bootstrap="/path/to/bootstrap.php"
```

## Patchwork

[Patchwork](https://github.com/antecedent/patchwork) is a library that enables temporarily overwriting user-defined functions and static methods. This means you can better isolate your system under test by mocking your plugin's functions that are tested elsewhere. If Patchwork is turned on, WP_Mock will transparently use it behind the scenes. For most use cases, you won't need to worry about using it directly.

If you'd like to use Patchwork in your tests, you need to specifically turn it on before bootstrapping WP_Mock:

```php
WP_Mock::setUsePatchwork(true);
WP_Mock::bootstrap();
```

## Strict Mode

WP_Mock has a strict mode that developers may optionally enable. By default, it is disabled. If enabled, strict mode will cause tests to fail if they use previously mocked functions without first explicitly declaring an expectation for how that function will be used. This provides an easy way to enforce an extra layer of specificity in unit tests. 

Like using patchwork, strict mode has to be enabled before the WP_Mock framework is bootstrapped:

```php
WP_Mock::activateStrictMode();
WP_Mock::bootstrap();
```