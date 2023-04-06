# Mocking WordPress actions and filters

The [hooks and filters of the WordPress Plugin API](http://codex.wordpress.org/Plugin_API) are common (and preferred) entry points for third-party scripts, and WP_Mock makes it easy to test that these are being registered and executed within your code.

## Ensuring actions and filters are registered

Rather than attempting to mock `add_action()` or `add_filter()`, WP_Mock has built-in support for both of these functions: instead, use `WP_Mock::expectActionAdded()` and `WP_Mock::expectFilterAdded()`, respectively.

In the following example, our expectations will fail if the `MyClass::addHooks()` method do not call `add_action('save_post', [$this, 'myActionCallback'], 10, 2 )` _and_ `add_action('the_content', [$this, 'myFilterCallback'])`:

```php
use MyPlugin\MyClass;
use WP_Mock\Tools\TestCase as TestCase;

final class MyClassTest extends TestCase
{
    public function testHookExpectations() : void 
    {
        $classInstance = new MyClass();
    
        WP_Mock::expectActionAdded('save_post', [$classInstance, 'myActionCallback'], 10, 2);
        WP_Mock::expectFilterAdded('the_content', [$classInstance, 'myFilterCallback']);
    
        $classInstance->addHooks();
    }
}
```

It's important to note that the `$priority` and `$parameter_count` arguments (parameters 3 and 4 for both `add_action()` and `add_filter()`) are significant. If in our example our code used a different priority or a different number of arguments when setting the callbacks, the test would have failed.

If the actual instance of an expected class cannot be passed, `AnyInstance` can be used:

```php
WP_Mock::expectFilterAdded('the_content', [new \WP_Mock\Matcher\AnyInstance(Special::class), 'the_content']);
```

## Asserting that closures have been added as hook callbacks

Sometimes it's handy to add a [Closure](https://secure.php.net/manual/en/class.closure.php) as a WordPress hook instead of defining a function in the global namespace. To assert that such a hook has been added, you can perform assertions referencing the Closure class or a `callable` type:

```php
public function testAnonymousHookCallback() : void 
{
    WP_Mock::expectActionAdded('save_post', WP_Mock\Functions::type('callable'));
    WP_Mock::expectActionAdded('save_post', WP_Mock\Functions::type(Closure::class));
    WP_Mock::expectFilterAdded('the_content', WP_Mock\Functions::type('callable'));
    WP_Mock::expectFilterAdded('the_content', WP_Mock\Functions::type(Closure::class));
}
```

## Asserting that actions and filters are applied

Now that we're testing if we are adding actions and/or filters, the next step is to ensure our code is calling those hooks when expected.

For actions, we'll want to listen for `do_action()` to be called for our action name, so we'll use `WP_Mock::expectAction()`:

```php
public function testActionCallingFunction() : void
{
    WP_Mock::expectAction('my_action');

    MyClass::myMethod();
}
```

This test will fail if `MyClass::myMethod()` does not call `do_action('my_action')`. In situations where your code needs to trigger actions, this assertion makes sure the appropriate hooks are being triggered.

For filters, we can inject our own response to `apply_filters()` using `WP_Mock::onFilter()`.

Take the code below, for example:

```php
namespace MyPlugin;

class MyClass
{
    public function filterContent() : string
    {
        return apply_filters('custom_content_filter', 'This is unfiltered');
    }
}
```

We can test that the filter is being applied by using `WP_Mock::onFilter()`:

```php
use MyPlugin\MyClass;
use WP_Mock;
use WP_Mock\Tools\TestCase as TestCase;

final class MyClassTest extends TestCase
{
    public function testCanFilterContent() : void 
    {
        WP_Mock::onFilter('custom_content_filter')
            ->with('This is unfiltered')
            ->reply('This is filtered');

        $content = (new MyClass())->filterContent();

        $this->assertEquals('This is filtered', $content);
    }
}
```

Alternatively, there is a method `WP_Mock::expectFilter()` that will add a bare assertion that the filter will be applied without changing the value:

```php
namespace MyPlugin;

class MyClass
{
    public function filterContent() : string 
    {
        $value = apply_filters( 'custom_content_filter', 'Default' );

        if ($value === 'Default') {
            do_action('default_value');
        }

        return $value;
    }
}
```

And then the test:

```php
use MyPlugin\MyClass;
use WP_Mock;
use WP_Mock\Tools\TestCase as TestCase;

final class MyClassTest extends TestCase
{
    public function testCanFilterContent() : void
    {
        WP_Mock::expectFilter('custom_content_filter', 'Default');
        WP_Mock::expectAction('default_value');

        $this->assertEquals('Default', (new MyClass())->filterContent());
    }
}
```