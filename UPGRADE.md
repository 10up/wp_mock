# Upgrade Guide

## 1.x → 2.0

WP_Mock 2.0 modernizes the test harness for current PHPUnit. The headline change is a higher floor:

> **WP_Mock 2.0 requires PHP 8.1+ and PHPUnit 10+.**
> If your project runs on **PHP 7.4 or 8.0**, or is pinned to **PHPUnit 9**, stay on WP_Mock **1.x** — it continues to work. WP_Mock 2.0 supports PHPUnit 10, 11, 12 and 13 from a single codebase.

Composer installs the highest PHPUnit version compatible with your PHP:

| Your PHP | PHPUnit installed |
|----------|-------------------|
| 8.1      | 10                |
| 8.2      | 11                |
| 8.3      | 12                |
| 8.4      | 13                |

For most projects on a supported PHP/PHPUnit, upgrading is just:

```shell
composer require --dev 10up/wp_mock:^2.0
```

The public assertion API (`assertConditionsMet()`, `assertHooksAdded()`, `assertActionsCalled()`, `assertEqualsHtml()`, `mockStaticMethod()`, …) is unchanged.

There are two behavior changes to be aware of.

### 1. Deprecated-method detection

If you (or your tooling) relied on WP_Mock marking a test **risky** when a deprecated WP_Mock method was called, that signal is now a native PHP **deprecation** (`E_USER_DEPRECATED`) surfaced through PHPUnit's own deprecation reporting.

To make these fail your test suite, set `failOnDeprecation="true"` on the root `<phpunit>` element of your configuration. With default configuration, the deprecation is reported but does not fail the run.

### 2. Output assertions

Earlier versions overrode PHPUnit's `expectOutputString()` to automatically strip tabs and newlines from output (toggled with the `@stripTabsAndNewlinesFromOutput` annotation). PHPUnit 10 removed `setOutputCallback()` and made `expectOutputString()` `final`, so this is no longer possible.

If you relied on that whitespace-insensitive output matching, switch to the new helper:

```php
// Before (1.x): output was silently stripped of tabs/newlines before comparison
$this->expectOutputString('<div>Test</div>');
my_function_that_echoes_html();

// After (2.0): wrap the output-producing code in a callback
$this->assertOutputEqualsHtml('<div>Test</div>', static function () {
    my_function_that_echoes_html();
});
```

PHPUnit's native `expectOutputString()` is still available if you want an **exact** (whitespace-sensitive) match.

### Removed internals

These were internal/rarely-used and are gone in 2.0 (they depended on PHPUnit APIs removed in PHPUnit 10):

- `WP_Mock\DeprecatedMethodListener::setTestResult()`, `setTestCase()`, `checkCalls()`
- The `WP_Mock\Tools\TestCase::run()` override
- The `@stripTabsAndNewlinesFromOutput` annotation and `TestCase::stripTabsAndNewlines()`

`WP_Mock::getDeprecatedMethodListener()` and `DeprecatedMethodListener::logDeprecatedCall()` remain available.
