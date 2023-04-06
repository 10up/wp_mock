# Using WP_Mock

With WP_Mock you can write your PHPUnit test cases as you normally would, but with the added benefit of being able to mock WordPress functions and classes.

## Mocking WordPress core functions

Ideally, a unit test will not depend on WordPress being loaded in order to test our code. By constructing **mocks**, it's possible to simulate WordPress core functionality by defining their expected arguments, responses, the number of times they are called, and more. In WP_Mock, this is done via the `WP_Mock::userFunction()` method:

Suppose you have the following method in your code that uses `get_post()` to output some content.

```php
namespace MyPlugin;

class MyClass
{
    public function myFunction(int $postId) : string
    {
        $post = get_post($postId);
        
        return  $post ? $post->post_content : 'Post not found'; 
    }
}
```

You can use `WP_Mock::userFunction()` to mock the `get_post()` function and return a mock post object:

```php
use MyPlugin\MyClass;
use PHPUnit\Framework\TestCase;
use stdClass
use WP_Mock;

final class MyClassTest extends TestCase
{
    public function testMyFunction() : void
    {
        $post = new stdClass();
        $post->post_content = 'Hello World'; 
        
        WP_Mock::userFunction('get_post')
            ->once()
            ->with(123)
            ->andReturn($post);
            
        $this->assertSame('Hello World', (MyClass::myFunction(123));
    }
}
```

In the above example WP_Mock is expecting that the method `MyClass::myFunction`, when invoked, it will in turn call `get_post()` exactly once, with a single argument of `123` as passed to the method's only argument, and that will return the content of a hypothetical post having that ID.

Calling `WP_Mock::userFunction()` will dynamically define the function for you if necessary, which means changes the internal WP_Mock API shouldn't break your mocks. If you really want to define your own function mocks, they should always end with this line:

```php
return WP_Mock\Handler::handle_function(__FUNCTION__, func_get_args());
```

## Using Mockery expectations

The `WP_Mock::userFunction()` class will return a complete `Mockery\Expectation` object with any expectations added to match the arguments passed to the function. This enables using [Mockery methods](http://docs.mockery.io/en/latest/reference/expectations.html) to add expectations in addition to, or instead of using the arguments array passed to `userFunction`.

For example, the invocation below will set the expectation that the `get_permalink` function will be called exactly once, with the argument `42`, and that it will return the string `'https://example.com/foo'`.

```php
WP_Mock::userFunction('get_permalink')->once()->with(42)->andReturn('https://example.com/foo');
```

## Using expectations in arguments

You can also pass an associative array of arguments to the second parameter of `WP_Mock::userFunction()` to set expectations about the function's arguments, the number of times it should be called, and what it should return.

### Arguments

The `args` parameter sets expectations about what the arguments passed to the function should be. This value should always be an array with the arguments in order and, like with return, if you use a `Closure`, its return value will be used to validate the argument expectations. You can also indicate that the argument can be any value of any type by using '`*`'.

WP_Mock has several helper functions to make this feature more flexible. There are static methods on the `WP_Mock\Functions` class meant for this:

* `Functions::type($type)`: Expects an argument of a certain type. This can be any core PHP data type (`string`, `int`, `resource`, `callable`, etc.) or any class or interface name.
* `Functions::anyOf($values)`: Expects the argument to be any value in the `$values` array.

#### Examples

In the following example, we're expecting `get_post_meta()` twice: once each for `some_meta_key` and `another_meta_key`, where an integer (in this case, a post ID) is the first argument, the meta key is the second, and a boolean `true` is the third.

```php
use WP_Mock;

WP_Mock::userFunction('get_post_meta', [
    'times' => 1,
    'args'  => [WP_Mock\Functions::type('int'), 'some_meta_key', true],
) );

WP_Mock::userFunction('get_post_meta', [
    'times' => 1,
    'args'  => [WP_Mock\Functions::type('int'), 'another_meta_key', true], 
) );
```

### Times

The `times` argument, as shown in the previous examples, declares how many times the given function should be called. For an exact number of calls, use a non-negative, numeric value (e.g. `3`). If the function should be called a minimum number of times, append a plus-sign (`+`, e.g. `7+` for seven or more calls). Conversely, if a mocked function should have a maximum number of invocations, append a minus-sign (`-`) to the argument (e.g. `7-` for seven or fewer times).

You may also choose to specify a range, e.g. `3-6` would translate to "this function should be called between three and six times".

The default value for `times` is `0+`, meaning the function should be called any number of times.

### Return

The `return` argument defines the value (if any) that the function should return. If you pass a `Closure` as the return value, the function will return whatever the Closure's return value is.

#### Example

```php
WP_Mock::userFunction('get_post_meta', [
    'return' => function($post_id, $key, $single) {
        if ($key === 'some_meta_key') {
            return 'some value';
        }
        
        return 'another value';
    }
) );
```

### Return in order

The `return_in_order` argument sets an array of values that should be returned with each subsequent call, useful if if your function will be called multiple times in the test but needs to return different values.

**Note:** Setting this value overrides whatever may be set `return`.

#### Example

```php
WP_Mock::userFunction('is_single', [
    'return_in_order' => [true, false],
]);

$this->assertTrue(is_single());
$this->assertFalse(is_single());
$this->assertFalse(is_single()); // All subsequent calls will use the last defined return value
```
### Return argument

You can use the `return_arg` argument to specify that the function should return one of its arguments. `return_arg` should be the position of the argument in the arguments array, so `0` for the first argument, `1` for the second, etc. You can also set this to `true`, which is equivalent to `0`. This will override both `return` and `return_in_order`.

#### Example

```php
WP_Mock::userFunction('sanitize_title', [
    'return_arg' => 0,
]);

// ...

sanitize_title($title); // WP_Mock will have this function return the value of $title as-is
```

