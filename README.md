# WP_Mock

WP_Mock is an API mocking framework, built and maintained by [10up](http://10up.com) for the purpose of making it possible to properly unit test within WordPress.


## Installation

First, add WP Mock as a dev-dependency with [Composer](http://getcomposer.org):

```bash
composer require --dev 10up/wp_mock:dev-master
```

Then, make sure your bootstrap file is loading the composer autoloader:

```php
require_once 'vendor/autoload.php';
```

Finally, register calls inside your test class to instantiate and clean up the `WP_Mock` object:

```php
class MyTestClass extends PHPUnit_Framework_TestCase {
	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}
}
```

## Using WP_Mock

Write your tests as you normally would. If you desire specific responses from WordPress API calls, wire those specifically.

```php
class MyTestClass extends PHPUnit_Framework_TestCase {
	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	 * The regular `get_the_content()` function is supposed to apply the 'the_content' filter to `post_content`.
	 * This test verifies that is the case.
	 */
	public function test_content_filter() {
		\WP_Mock::onFilter( 'the_content' )->with( 'Windows Rocks!' )->reply( 'Apple Rocks!' );

		$post = new stdClass;
		$post->post_content = 'Windows Rocks!';
		setup_postdata( $post );

		$content = get_the_content();

		$this->assertEquals( 'Apple Rocks!', $content );
	}

	/**
	 * Our special_action function must actually invoke the 'special_action' action when it's done.
	 * function special_action() {
	 *     // ... do stuff
	 *     do_action( 'special_action' );
	 * }
	 */
	public function test_method_has_action() {
		\WP_Mock::expectAction( 'special_action' );

		// If this function does not call `do_action( 'special_action' )`, the test will fail.
		special_action();
	}

	/**
	 * Our init function must add an action to save_post and a filter to the_content
	 * function special_init() {
	 *     add_action( 'save_post', 'special_save_post', 10, 2 );
	 *     add_filter( 'the_content', 'special_the_content' );
	 * }
	 */
	public function test_init() {
		\WP_Mock::expectActionAdded( 'save_post', 'special_save_post', 10, 2 );
		\WP_Mock::expectFilterAdded( 'the_content', 'special_the_content' );
		// If this function does not add the action and filter we expect with the correct priority and argument count, the test will fail.
		special_init();
	}
}
```

### Mocking WordPress core functions

Ideally, a unit test will not depend on WordPress being loaded in order to test our code. By constructing **mocks**, it's possible to simulate WordPress core functionality by defining their expected arguments, responses, the number of times they are called, and more. In WP_Mock, this is done via the `\WP_Mock::wpFunction()` method:

```php
public function test_uses_get_post() {
	global $post;

	$post = new \stdClass;
	$post->ID = 42;
	$post->special_meta = '<p>I am on the end</p>';

	\WP_Mock::wpFunction( 'get_post', array(
		'times' => 1,
		'args' => array( $post->ID ),
		'return' => $post,
	) );

	// Let's say our function gets the post and appends a value stored in 'special_meta' to the content
	$results = special_the_content( '<p>Some content</p>' );

	// In addition to failing if this assertion is false, the test will fail if get_post is not called with the arguments above
	$this->assertEquals( '<p>Some content</p><p>I am on the end</p>', $results );
}
```

In the example above, we're creating a simple `\stdClass` to represent a response from `get_post()`, setting the `ID` and `special_meta` properties. WP_Mock is expecting `get_post()` to be called exactly once, with a single argument of '42', and for the function to return our `$post` object.

With our expectations set, we call `special_the_content()`, the function we're testing, then asserting that what we get back from it is equal to `<p>Some content</p><p>I am on the end</p>`, which proves that `special_the_content()` appended `$post->special_meta` to `<p>Some content</p>`.

Calling `\WP_Mock::wpFunction()` will dynamically define the function for you if necessary, which means changes the internal WP_Mock API shouldn't break your mocks. If you really want to define your own function mocks, they should always end with this line:

```php
return \WP_Mock\Handler::handle_function( __FUNCTION__, func_get_args() );
```

`\WP_Mock::wpFunction()` accepts an associative array of arguments for its second parameter:

#### args

Sets expectations about what the arguments passed to the function should be. This value should always be an array with the arguments in order and, like with return, if you use a `\Closure`, its return value will be used to validate the argument expectations. You can also indicate that the argument can be any value of any type by using '`*`'.

WP_Mock has several helper functions to make this feature more flexible. The are static methods on the `\WP_Mock\Functions` class. They are:

* `Functions::type( $type )`: Expects an argument of a certain type. This can be any core PHP data type (`string`, `int`, `resource`, `callable`, etc.) or any class or interface name.
* `Functions::anyOf( $values )`: Expects the argument to be any value in the `$values` array.

##### Examples

In the following example, we're expecting `get_post_meta()` twice: once each for `some_meta_key` and `another_meta_key`, where an integer (in this case, a post ID) is the first argument, the meta key is the second, and a boolean TRUE is the third.

```php
\WP_Mock::wpFunction( 'get_post_meta', array(
	'times' => 1,
	'args' => array( \WP_Mock\Functions::type( 'int' ), 'some_meta_key', true )
) );

\WP_Mock::wpFunction( 'get_post_meta', array(
	'times' => 1,
	'args' => array( \WP_Mock\Functions::type( 'int' ), 'another_meta_key', true )
) );
```

#### times

Declares how many times the given function should be called. For an exact number of calls, use a non-negative, numeric value (e.g. `3`). If the function should be called a minimum number of times, append a plus-sign (`+`, e.g. `7+` for seven or more calls). Conversely, if a mocked function should have a maximum number of invocations, append a minus-sign (`-`) to the argument (e.g. `7-` for seven or fewer times).

You may also choose to specify a range, e.g. `3-6` would translate to "this function should be called between three and six times".

The default value for `times` is `0+`, meaning the function should be called any number of times.

#### return

Defines the value (if any) that the function should return. If you pass a `\Closure` as the return value, the function will return whatever the Closure's return value is.

#### return_in_order

Set an array of values that should be returned with each subsequent call, useful if if your function will be called multiple times in the test but needs to return different values.

**Note:** Setting this value overrides whatever may be set `return`.

##### Example

```php
\WP_Mock::wpFunction( 'is_single', array(
	'return_in_order' => array( true, false )
) );

$this->assertTrue( is_single() );
$this->assertFalse( is_single() );
$this->assertFalse( is_single() ); // All subsequent calls will use the last defined return value
```
#### return_arg

Use this to specify that the function should return one of its arguments. `return_arg` should be the position of the argument in the arguments array, so `0` for the first argument, `1` for the second, etc. You can also set this to `true`, which is equivalent to `0`. This will override both `return` and `return_in_order`.

### Passthru functions

It's not uncommon for tests to need to declare "passthrough/passthru" functions: empty functions that just return whatever they're passed (remember: you're testing your code, not the framework). In these situtations you can use `\WP_Mock::wpPassthruFunction( 'function_name' )`, which is equivalent to the following:

```php
\WP_Mock::wpFunction( 'function_name', array(
	'return_arg' => 0
) );
```

You can still test things like invocation count by passing the `times` argument in the second parameter, just like `\WP_Mock::wpFunction()`.