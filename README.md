WP_Mock
=======

WordPress API Mocking Framework

Use
--------

First, add WP Mock as a dev-dependency with [Composer](http://getcomposer.org):

```
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

When you need to mock core WordPress functions, such as `get_post()`, use `\WP_Mock::wpFunction()`:

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

`\WP_Mock::wpFunction()` will dynamically define the function for you if necessary. Generally, it's best to let it do so, just in case we need to change the internal API, your mocks will continue to work. If you really want to define your own function mocks, they should always end with this line:

```php
return \WP_Mock\Handler::handle_function( __FUNCTION__, func_get_args() );
```

`\WP_Mock::wpFunction()`'s second parameter accepts a few arguments as an associative array:

* `times`: Defines expectations for the number of times a function should be called. The default is 0 or more times. To expect the function to be called an exact amount of times, set times to a non-negative numeric value. To specify that the function should be called a minimum number of times, use a string with the minimum followed by `'+'` (e.g. `'3+'` means 3 or more times). Append a `'-'` to indicate a maximum number of times a function should be called (e.g. `'3-'` means no more than 3 times). To indicate a range, use `'-'` between two numbers (e.g. `'2-5'` means at least 2 times and no more than 5 times).
* `return`: Defines the value (if any) that the function should return. If you pass a `\Closure` as the return value, the function will return whatever the Closure's return value is.
* `return_in_order`: Use this if your function will be called multiple times in the test but needs to have different return values. Set this to an array of return values. Each time the function is called, it will return the next value in the sequence until it reaches the last value, which will become the return value for all subsequent calls. For example, if I am mocking `is_single()`, I can set return_in_order to `array( false, true )`. The first time `is_single()` is called it will return `false`. The second and all subsequent times it will return `true`. Setting this value overrides `'return'`, so if you set both, `'return'` will be ignored.
* `return_arg`: Use this to specify that the function should return one of its arguments. return_arg should be the position of the argument in the arguments array, so `0` for the first argument, `1` for the second, etc. You can also set this to `true`, which is equivalent to `0`. This will override both `return` and `return_in_order`.
* `args`: Use this to set expectations about what the arguments passed to the function should be. This value should always be an array with the arguments in order. Like with return, if you use a `\Closure`, its return value will be used to validate the argument expectations. Also, you can indicate that the argument can be any value of any type by using `'*'`. WP_Mock has several helper functions to make this feature more flexible. The are static methods on the `\WP_Mock\Functions` class. They are:
    * `Functions::type( $type )`: Expects an argument of a certain type. This can be any core PHP data type (`string`, `int`, `resource`, `callable`, etc.) or any class or interface name.
    * `Functions::anyOf( $values )`: Expects the argument to be any value in the `$values` array

So, for example, if I am expecting `get_post_meta()` to be called, the `'args'` array might look something like this:

```php
array( $post->ID, 'some_meta_key', true )
```
