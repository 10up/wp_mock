# Mocking WordPress objects

Mocking calls to `wpdb`, `WP_Query`, etc. can be done using the [Mockery](https://github.com/padraic/mockery) framework.  While this isn't part of WP Mock itself, complex code will often need these objects and this framework will let you incorporate those into your tests. Since WP Mock requires Mockery, it should already be included as part of your installation.

## An example with `WPDB`

Let's say we have a function that gets three post IDs from the database.

```php
namespace MyPlugin;

class MyClass
{
    public function getSomePostIds() : array 
    {
        global $wpdb;
        return $wpdb->get_col("SELECT ID FROM {$wpdb->posts} LIMIT 3");
    }
}
```

When we mock the `$wpdb` object, we're not performing an actual database call, only mocking the results.  We need to call the `get_col` method with an SQL statement, and return three arbitrary post IDs.

```php
use Mockery;
use MyPlugin\MyClass;
use PHPUnit\Framework\TestCase;

final class MyClassTest extends TestCase
{
    public function testCanGetSomePostIds() : void
    {
        global $wpdb;

        $wpdb = Mockery::mock('WPDB');
        $wpdb->posts = 'wp_posts';

        $wpdb->allows('get_col')
            ->once()
            ->with('SELECT ID FROM wp_posts LIMIT 3')
            ->andReturn([1, 2, 3]);

        $postIds = (new MyClass())->getSomePostIds();

        $this->assertEquals([1, 2, 3], $postIds);
    }
}
```
