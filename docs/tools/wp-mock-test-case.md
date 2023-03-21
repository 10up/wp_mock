# WP_Mock Test Case

WP_Mock comes with a base test case class that provides a number of useful methods for testing WordPress plugins and themes.

This class is located in the `WP_Mock\Tools\TestCase` namespace, and can be used by extending it in your test classes:

```php
use WP_Mock\Tools\TestCase as TestCase;

final class MyTestCase extends TestCase
{
    // ...
}
```

WP_Mock `TestCase` extends PHPUnit own `TestCase` so all methods and assertions from the latter are available in the former.

On top of those, WP_Mock `TestCase` will include a collection of handy methods for helping you test your code.

## Methods


### Assert conditions met

The `TestCase::assertConditionsMet()` function will assert that the current test conditions have been met. This is useful when your test assertions are purely WP_Mock expectations, and you don't want to have to call `Mockery::close()` in your test, otherwise PHPUnit might raise a warning that no assertions were performed.

```php
use WP_Mock\Tools\TestCase as TestCase;

final class MyTestCase extends TestCase
{
    public function testMyFunction() : void
    {
        WP_Mock::userFunction('my_function', ['times' => 1]);
        
        $this->assertConditionsMet(); 
    }
}
```

### Assert equals HTML

The `TestCase::assertEqualsHtml()` function will evaluate a string as HTML and compare it to another string. This is useful when you want to compare HTML strings that may have different formatting, but are otherwise identical.

```php
use WP_Mock\Tools\TestCase as TestCase;

final class MyTestCase extends TestCase
{
    public function testMyFunction() : void
    {
        $this->assertEqualsHtml('<div>Test</div>', '<div>Test</div>');
    }
}
```

### Expect output string

The `TestCase::expectOutputString()` function will assert that the output of a function matches a given string. This is useful when you want to test the output of a function that echoes HTML.

```php
use WP_Mock\Tools\TestCase as TestCase;

final class MyTestCase extends TestCase
{
    public function testMyFunction() : void
    {
        $this->expectOutputString('<div>Test</div>');
        
        echo '<div>Test</div>';
    }   
}
```

### Mock static method

The `TestCase::mockStaticMethod()` function will mock a static method on a class, via Patchwork, returning a Mockery object. 

```php
use WP_Mock\Tools\TestCase as TestCase;

final class MyTestCase extends TestCase
{
    public function testMyFunction() : void
    {
        $mock = $this->mockStaticMethod('MyClass', 'myStaticMethod');
        $mock->expects($this->once())->willReturn('test');
        
        $this->assertEquals('test', MyClass::myStaticMethod());
    }   
}
```