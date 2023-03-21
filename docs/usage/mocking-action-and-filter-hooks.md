# Mocking WordPress actions and filters

The [hooks and filters of the WordPress Plugin API](http://codex.wordpress.org/Plugin_API) are common (and preferred) entry points for third-party scripts, and WP_Mock makes it easy to test that these are being registered and executed within your code.

#### Ensuring actions and filters are registered

Rather than attempting to mock `add_action()` or `add_filter()`, WP_Mock has built-in support for both of these functions: instead, use `\WP_Mock::expectActionAdded()` and `\WP_Mock::expectFilterAdded()` (respectively). In the following example, our `test_special_function()` test will fail if `special_function()` doesn't call `add_action( 'save_post', 'special_save_post', 10, 2 )` _and_ `add_filter( 'the_content', 'special_the_content' )`:

```php
public function test_special_function() {
    WP_Mock::expectActionAdded( 'save_post', 'special_save_post', 10, 2 );
    WP_Mock::expectFilterAdded( 'the_content', 'special_the_content' );

    my_special_function();
}
```

It's important to note that the `$priority` and `$parameter_count` arguments (parameters 3 and 4 for both `add_action()` and `add_filter()`) are significant. If `special_function()` were to call `add_action( 'save_post', 'special_save_post', 99, 3 )` instead of the expected `add_action( 'save_post', 'special_save_post', 10, 2 )`, our test would fail.

If the actual instance of an expected class cannot be passed, `AnyInstance` can be used:

```php
\WP_Mock::expectFilterAdded( 'the_content', array( new \WP_Mock\Matcher\AnyInstance( Special::class ), 'the_content' ) );
```

#### Asserting that closures have been added as hook callbacks

Sometimes it's handy to add a [Closure](https://secure.php.net/manual/en/class.closure.php) as a WordPress hook instead of defining a function in the global namespace. To assert that such a hook has been added, you can perform assertions referencing the Closure class or a `callable` type:

```php
public function test_anonymous_function_hook() {
	\WP_Mock::expectActionAdded('save_post', \WP_Mock\Functions::type('callable'));
	\WP_Mock::expectActionAdded('save_post', \WP_Mock\Functions::type(Closure::class));
	\WP_Mock::expectFilterAdded('the_content', \WP_Mock\Functions::type('callable'));
	\WP_Mock::expectFilterAdded('the_content', \WP_Mock\Functions::type(Closure::class));
}
```

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
