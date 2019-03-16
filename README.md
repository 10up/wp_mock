# WP_Mock ![PHP 7.1+][php-image] [![Build Status][travis-image]][travis-url] [![Coverage Status][coveralls-image]][coveralls-url] [![Packagist][packagist-image]][packagist-url]

WP_Mock is an API mocking framework, built and maintained by [10up](http://10up.com) for the purpose of making it possible to properly unit test within WordPress.

<a href="http://10up.com/contact/"><img src="https://10updotcom-wpengine.s3.amazonaws.com/uploads/2016/10/10up-Github-Banner.png" width="850"></a>

## Installation

First, add WP Mock as a dev-dependency with [Composer](http://getcomposer.org):

```bash
composer require --dev 10up/wp_mock:0.4.2
```

Then, make sure your bootstrap file is loading the composer autoloader:

```php
require_once 'vendor/autoload.php';
```

Finally, register calls inside your test class to instantiate and clean up the `WP_Mock` object:

```php
class MyTestClass extends \WP_Mock\Tools\TestCase {
	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}
}
```

## Bootstrapping WP_Mock

Before you can start using WP_Mock to test your code, you'll need to bootstrap the library. The easiest way is to use a bootstrap file. See the PHPUnit documentation for how to define a bootstrap script either [from the command line](https://phpunit.de/manual/current/en/textui.html#textui.clioptions) or [from the xml config file](https://phpunit.de/manual/current/en/appendixes.configuration.html). Here is an example of a bootstrap you might use:

```php
<?php

// First we need to load the composer autoloader so we can use WP Mock
require_once __DIR__ . '/vendor/autoload.php';

// Now call the bootstrap method of WP Mock
WP_Mock::bootstrap();

/**
 * Now we include any plugin files that we need to be able to run the tests. This
 * should be files that define the functions and classes you're going to test.
 */
require_once __DIR__ . '/plugin.php';
```

The bootstrap method does a few things:

- Defines action and filter functions
- Defines some common WordPress constants
- Sets up Patchwork if it has been turned on

If you'd like to use Patchwork in your tests, you need to specifically turn it on before bootstrapping WP_Mock:

```php
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
```

Patchwork is a library that enables temporarily overwriting user-defined functions and static methods. This means you can better isolate your system under test by mocking your plugin's functions that are tested elsewhere. If Patchwork is turned on, WP_Mock will transparently use it behind the scenes. For most use cases, you won't need to worry about using it directly.

### Strict Mode

WP_Mock has a strict mode that developers may optionally activate. By default, it is off and not the default behavior. If activated, strict mode will cause tests to fail if they use previously mocked functions without first explicitly declaring an expectation for how that function will be used. This provides an easy way to enforce an extra layer of specificity in unit tests. Previously, the only way to simulate a strict mode was to run every single test in process isolation. Like using patchwork, strict mode has to be turned on *before* the WP_Mock framework is bootstrapped:

```php
WP_Mock::activateStrictMode();
WP_Mock::bootstrap();
```

WP_Mock will ignore any attempts to activate strict mode after the first time it is bootstrapped.

## Using WP_Mock

Write your tests as you normally would. If you desire specific responses from WordPress API calls, wire those specifically.

```php
class MyTestClass extends \WP_Mock\Tools\TestCase {
	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	 * Assume that my_permalink_function() is meant to do all of the following:
	 * - Run the given post ID through absint()
	 * - Call get_permalink() on the $post_id
	 * - Pass the permalink through the 'special_filter' filter
	 * - Trigger the 'special_action' WordPress action
	 */
	public function test_my_permalink_function() {
		\WP_Mock::userFunction( 'get_permalink', array(
			'args' => 42,
			'times' => 1,
			'return' => 'http://example.com/foo'
		) );

		\WP_Mock::passthruFunction( 'absint', array( 'times' => 1 ) );

		\WP_Mock::onFilter( 'special_filter' )
			->with( 'http://example.com/foo' )
			->reply( 'https://example.com/bar' );

		\WP_Mock::expectAction( 'special_action', 'https://example.com/bar' );

		$result = my_permalink_function( 42 );

		$this->assertEquals( 'https://example.com/bar', $result );
	}
}
```

The function being described by our tests would look something like this:

```php
/**
 * Get a post's permalink, then run it through special filters and trigger
 * the 'special_action' action hook.
 *
 * @param int $post_id The post ID being linked to.
 * @return str|bool    The permalink or a boolean false if $post_id does
 *                     not exist.
 */
function my_permalink_function( $post_id ) {
	$permalink = get_permalink( absint( $post_id ) );
	$permalink = apply_filters( 'special_filter', $permalink );

	do_action( 'special_action', $permalink );

	return $permalink;
}
```

### Mocking WordPress core functions

Ideally, a unit test will not depend on WordPress being loaded in order to test our code. By constructing **mocks**, it's possible to simulate WordPress core functionality by defining their expected arguments, responses, the number of times they are called, and more. In WP_Mock, this is done via the `\WP_Mock::userFunction()` method:

```php
public function test_uses_get_post() {
	global $post;

	$post = new \stdClass;
	$post->ID = 42;
	$post->special_meta = '<p>I am on the end</p>';

	\WP_Mock::userFunction( 'get_post', array(
		'times' => 1,
		'args' => array( $post->ID ),
		'return' => $post,
	) );

	/*
	 * Let's say our function gets the post and appends a value stored in
	 * 'special_meta' to the content.
	 */
	$results = special_the_content( '<p>Some content</p>' );

	/*
	 * In addition to failing if this assertion is false, the test will fail
	 * if get_post is not called with the arguments above.
	 */
	$this->assertEquals( '<p>Some content</p><p>I am on the end</p>', $results );
}
```

In the example above, we're creating a simple `\stdClass` to represent a response from `get_post()`, setting the `ID` and `special_meta` properties. WP_Mock is expecting `get_post()` to be called exactly once, with a single argument of '42', and for the function to return our `$post` object.

With our expectations set, we call `special_the_content()`, the function we're testing, then asserting that what we get back from it is equal to `<p>Some content</p><p>I am on the end</p>`, which proves that `special_the_content()` appended `$post->special_meta` to `<p>Some content</p>`.

Calling `\WP_Mock::userFunction()` will dynamically define the function for you if necessary, which means changes the internal WP_Mock API shouldn't break your mocks. If you really want to define your own function mocks, they should always end with this line:

```php
return \WP_Mock\Handler::handle_function( __FUNCTION__, func_get_args() );
```

#### Setting expectations

`\WP_Mock::userFunction()` accepts an associative array of arguments for its second parameter:

##### args

Sets expectations about what the arguments passed to the function should be. This value should always be an array with the arguments in order and, like with return, if you use a `\Closure`, its return value will be used to validate the argument expectations. You can also indicate that the argument can be any value of any type by using '`*`'.

WP_Mock has several helper functions to make this feature more flexible. The are static methods on the `\WP_Mock\Functions` class. They are:

* `Functions::type( $type )`: Expects an argument of a certain type. This can be any core PHP data type (`string`, `int`, `resource`, `callable`, etc.) or any class or interface name.
* `Functions::anyOf( $values )`: Expects the argument to be any value in the `$values` array.

###### Examples

In the following example, we're expecting `get_post_meta()` twice: once each for `some_meta_key` and `another_meta_key`, where an integer (in this case, a post ID) is the first argument, the meta key is the second, and a boolean TRUE is the third.

```php
\WP_Mock::userFunction( 'get_post_meta', array(
	'times' => 1,
	'args' => array( \WP_Mock\Functions::type( 'int' ), 'some_meta_key', true )
) );

\WP_Mock::userFunction( 'get_post_meta', array(
	'times' => 1,
	'args' => array( \WP_Mock\Functions::type( 'int' ), 'another_meta_key', true )
) );
```

##### times

Declares how many times the given function should be called. For an exact number of calls, use a non-negative, numeric value (e.g. `3`). If the function should be called a minimum number of times, append a plus-sign (`+`, e.g. `7+` for seven or more calls). Conversely, if a mocked function should have a maximum number of invocations, append a minus-sign (`-`) to the argument (e.g. `7-` for seven or fewer times).

You may also choose to specify a range, e.g. `3-6` would translate to "this function should be called between three and six times".

The default value for `times` is `0+`, meaning the function should be called any number of times.

##### return

Defines the value (if any) that the function should return. If you pass a `\Closure` as the return value, the function will return whatever the Closure's return value is.

##### return_in_order

Set an array of values that should be returned with each subsequent call, useful if if your function will be called multiple times in the test but needs to return different values.

**Note:** Setting this value overrides whatever may be set `return`.

###### Example

```php
\WP_Mock::userFunction( 'is_single', array(
	'return_in_order' => array( true, false )
) );

$this->assertTrue( is_single() );
$this->assertFalse( is_single() );
$this->assertFalse( is_single() ); // All subsequent calls will use the last defined return value
```
##### return_arg

Use this to specify that the function should return one of its arguments. `return_arg` should be the position of the argument in the arguments array, so `0` for the first argument, `1` for the second, etc. You can also set this to `true`, which is equivalent to `0`. This will override both `return` and `return_in_order`.

### Using Mockery expectations

The return value of `\WP_Mock::userFunction` will be a complete `Mockery\Mock` object with any expectations added to match the arguments passed to the function. This enables using [Mockery methods](http://docs.mockery.io/en/latest/reference/expectations.html) to add expectations in addition to, or instead of using the arguments array passed to `userFunction`.

For example, the following are synonymous:

```php
\WP_Mock::userFunction( 'get_permalink', array( 'args' => 42, 'return' => 'http://example.com/foo' ) );
```

```php
\WP_Mock::userFunction( 'get_permalink' )->with( 42 )->andReturn( 'http://example.com/foo' );
```

### Passthru functions

It's not uncommon for tests to need to declare "passthrough/passthru" functions: empty functions that just return whatever they're passed (remember: you're testing your code, not the framework). In these situations you can use `\WP_Mock::passthruFunction( 'function_name' )`, which is equivalent to the following:

```php
\WP_Mock::userFunction( 'function_name', array(
	'return_arg' => 0
) );
```

You can still test things like invocation count by passing the `times` argument in the second parameter, just like `\WP_Mock::userFunction()`.

### Deprecated methods

Please note that `WP_Mock::wpFunction()` and `WP_Mock::wpPassthruFunction()` are both officially deprecated. Replace all uses of them with `WP_Mock::userFunction()` and `WP_Mock::passthruFunction()`. If you use either of the deprecated methods, WP_Mock will mark those tests as risky. Your tests will still count as passing, but PHPUnit will start telling you which tests are causing issues.

### Mocking actions and filters

The [hooks and filters of the WordPress Plugin API](http://codex.wordpress.org/Plugin_API) are common (and preferred) entry points for third-party scripts, and WP_Mock makes it easy to test that these are being registered and executed within your code.

#### Ensuring actions and filters are registered

Rather than attempting to mock `add_action()` or `add_filter()`, WP_Mock has built-in support for both of these functions: instead, use `\WP_Mock::expectActionAdded()` and `\WP_Mock::expectFilterAdded()` (respectively). In the following example, our `test_special_function()` test will fail if `special_function()` doesn't call `add_action( 'save_post', 'special_save_post', 10, 2 )` _and_ `add_filter( 'the_content', 'special_the_content' )`:

```php
public function test_special_function() {
	\WP_Mock::expectActionAdded( 'save_post', 'special_save_post', 10, 2 );
	\WP_Mock::expectFilterAdded( 'the_content', 'special_the_content' );

	special_function();
}
```

It's important to note that the `$priority` and `$parameter_count` arguments (parameters 3 and 4 for both `add_action()` and `add_filter()`) are significant. If `special_function()` were to call `add_action( 'save_post', 'special_save_post', 99, 3 )` instead of the expected `add_action( 'save_post', 'special_save_post', 10, 2 )`, our test would fail.

#### Asserting that actions and filters are applied

Now that we're testing whether or not we're adding actions and/or filters, the next step is to ensure our code is calling those hooks when expected.

For actions, we'll want to listen for `do_action()` to be called for our action name, so we'll use `\WP_Mock::expectAction()`:

```php
function test_action_calling_function () {
	\WP_Mock::expectAction( 'my_action' );

	action_calling_function();
}
```

This test will fail if `action_calling_function()` doesn't call `do_action( 'my_action' )`. In situations where your code needs to trigger actions, this assertion makes sure the appropriate hooks are being triggered.

For filters, we can inject our own response to `apply_filters()` using `\WP_Mock::onFilter()`:

```php
public function filter_content() {
	return apply_filters( 'custom_content_filter', 'This is unfiltered' );
}

public function test_filter_content() {
	\WP_Mock::onFilter( 'custom_content_filter' )
		->with( 'This is unfiltered' )
		->reply( 'This is filtered' );

	$response = $this->filter_content();

	$this->assertEquals( 'This is filtered', $response );
}
```

Alternatively, there is a method `\WP_Mock::expectFilter()` that will add a bare assertion that the filter will be applied without changing the value:

```php
class SUT {
	public function filter_content() {
		$value = apply_filters( 'custom_content_filter', 'Default' );
		if ( $value === 'Default' ) {
			do_action( 'default_value' );
		}

		return $value;
	}
}

class SUTTest {
	public function test_filter_content() {
		\WP_Mock::expectFilter( 'custom_content_filter', 'Default' );
		\WP_Mock::expectAction( 'default_value' );

		$this->assertEquals( 'Default', (new SUT)->filter_content() );
	}
}
```

### Mocking WordPress objects

Mocking calls to `wpdb`, `WP_Query`, etc. can be done using the [mockery](https://github.com/padraic/mockery) framework.  While this isn't part of WP Mock itself, complex code will often need these objects and this framework will let you incorporate those into your tests.  Since WP Mock requires Mockery, it should already be included as part of your install.

#### $wpdb example

Let's say we have a function that gets three post IDs from the database.
```php
function get_post_ids() {
	global $wpdb;
	return $wpdb->get_col( "select ID from {$wpdb->posts} LIMIT 3" );
}
```

When we mock the `$wpdb` object, we're not performing an actual database call, only mocking the results.  We need to call the `get_col` method with an SQL statement, and return three arbitrary post IDs.

```php
use Mockery;

function test_get_post_ids() {
	global $wpdb;

	$wpdb = Mockery::mock( '\WPDB' );
	$wpdb->shouldReceive( 'get_col' )
		->once()
		->with( "select ID from wp_posts LIMIT 3" )
		->andReturn( array( 1, 2, 3 ) );
	$wpdb->posts = 'wp_posts';

	$post_ids = get_post_ids();

	$this->assertEquals( array( 1, 2, 3 ), $post_ids );
}
```

## Credits

* [Eric Mann](https://github.com/ericmann)
* [John Bloch](https://github.com/johnpbloch)
* [All Contributors](https://github.com/10up/wp_mock/graphs/contributors)

## Contributing

Thanks so much for being interested in contributing! Please read over our [guidelines](https://github.com/10up/wp_mock/blob/dev/CONTRIBUTING.md) before you get started.

[php-image]: https://img.shields.io/badge/php-7.1%2B-green.svg
[packagist-image]: https://img.shields.io/packagist/dt/10up/wp_mock.svg
[packagist-url]: https://packagist.org/packages/10up/wp_mock
[travis-image]: https://travis-ci.org/10up/wp_mock.svg?branch=master
[travis-url]: https://travis-ci.org/10up/wp_mock
[coveralls-image]: https://coveralls.io/repos/github/10up/wp_mock/badge.svg?branch=master
[coveralls-url]: https://coveralls.io/github/10up/wp_mock?branch=master
