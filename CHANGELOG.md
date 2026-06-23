# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0](https://github.com/10up/wp_mock/compare/1.1.1...2.0.0) - Unreleased

WP_Mock now supports PHPUnit 9.6, 10, 11, 12 and 13 from a single codebase. The minimum PHP version is unchanged (7.4); Composer installs the highest PHPUnit version your PHP allows. Most projects can upgrade by bumping only `10up/wp_mock` — see [UPGRADE.md](https://github.com/10up/wp_mock/blob/trunk/UPGRADE.md).

### Added
- Support for PHPUnit 10, 11, 12 and 13 (in addition to 9.6)
- `TestCase::assertOutputEqualsHtml(string $expectedHtml, callable $callback)` for whitespace-insensitive assertions on echoed HTML output

### Changed
- **BREAKING:** deprecated WP_Mock methods now emit a native `E_USER_DEPRECATED` notice (via `trigger_error()`) instead of forcing a test to be marked "risky". To fail tests on these, set `failOnDeprecation="true"` (PHPUnit 10+) or `convertDeprecationsToExceptions="true"` (PHPUnit 9) in your configuration.
- **BREAKING:** removed the implicit output filtering and the `@stripTabsAndNewlinesFromOutput` annotation — including the `TestCase::expectOutputString()` override — because PHPUnit 10 removed `setOutputCallback()` and made `expectOutputString()` `final`. Use `assertOutputEqualsHtml()` (or PHPUnit's native `expectOutputString()` for exact matches).
- `IsEqualHtml` now implements `matches()`/`toString()` and takes a single constructor argument
- Raised minimum dependencies: `mockery/mockery ^1.6.12`, PHPStan tooling to `^2`
- Test runner configuration is split into `phpunit.xml.dist` (PHPUnit 10+) and `phpunit9.xml.dist` (PHPUnit 9)

### Removed
- The abandoned `sempro/phpunit-pretty-print` dev dependency (use `--testdox`)
- `DeprecatedMethodListener::setTestResult()`, `setTestCase()`, `checkCalls()` and the `TestCase::run()` override (relied on PHPUnit's removed `TestResult`/`RiskyTestError`)

### Fixed
- `WP_Mock::expectFilterNotAdded()` defaulted `$args` to `10` instead of `1`, so the "not added" expectation never matched a standard `add_filter()` call and silently passed; it now correctly guards the hook

## [1.1.1](https://github.com/10up/wp_mock/compare/1.1.0...1.1.1) - 2025-12-03
### Fixed
- Address PHP deprecation warnings about implicitly nullable parameters
- Allow `onFilter(...)->withAnyArgs()` to be used multiple times on a single tests

## [1.1.0](https://github.com/10up/wp_mock/compare/1.0.1...1.1.0) - 2025-03-11
### Added
- Add support for types in `expectFilter()` mocks
- Add support for `withAnyArgs()` method for `onFilter()` calls

### Fixed
- Prevent IDEs/phpstan from detecting exceptions in mocked functions that don't actually throw in WordPress core (e.g. `esc_html()`)  
- Typos in docs and function name (old function `iExcpectWhenIRun()` still exists but has been deprecated)

## [1.0.1](https://github.com/10up/wp_mock/compare/1.0.0...1.0.1) - 2024-01-04
### Changed
- Updated `WP_Mock::expectHookNotAdded()` with new params
- Updated PHP dependencies to ensure compatibility with PHP 8.3

## [1.0.0](https://github.com/10up/wp_mock/compare/0.5.0...1.0.0) - 2023-07-24
### Added
- Added and documented return types in codebase
- New `AccessInaccessibleClassMembers` helpers in test case
- Move [documentation to GitBook](https://wp-mock.gitbook.io/documentation/getting-started/introduction)
### Changed
- Require PHP 7.4
- Update dependencies (Mockery 1.6, PHPUnit 9.6)
- Remove deprecated methods in `WP_Mock` main class
- Update `Hook::safe_offset()` to handle closure
### Fixed
- `IsEqualHtml` extends PHPUnit `Constraint`
- Improve responder handling of closures 

## [0.5.0](https://github.com/10up/wp_mock/compare/0.4.2...0.5.0) - 2022-11-01
### Added
- New `AnyInstance` matcher
### Changed
- Add compatibility with PHPUnit 9
- Require PHP 7.3
- Mocker function for `_n()` to evaluate singular or plural
### Fixed
- Patchwork not loaded error (`WP_Mock::setUsePatchwork(true)` was broken)
- Address call to undefined method `getAnnotations` error from test class
- Support for latest PHPUnit versions

## [0.4.2](https://github.com/10up/wp_mock/compare/0.4.2...0.4.1) - 2019-03-15
### Added
- **Minor Filter/Action/Hook Assertion Bugfix**
- Please note: As with the previously-tagged release, this is not necessarily a stable release!

## [0.4.1](https://github.com/10up/wp_mock/compare/0.4.1...0.4.0) - 2019-02-26
### Added
- **PHPUnit 8 Compatibility**
- This release brings us up to date with the latest release of PHPUnit.
- Please note: As with the previously-tagged release, this is not necessarily a stable release!

## [0.4.0](https://github.com/10up/wp_mock/compare/0.4.0...0.3.0) - 2019-01-16
### Added
- **PHPUnit 7 Compatibility**
- This release brings us up to date both with PHPUnit and with PHP itself. The minimum version of PHP now supported by the project is *7.1*.
- *Please note:* As with the previously-tagged release, this is not necessarily a stable release!

## [0.3.0](https://github.com/10up/wp_mock/compare/0.3.0...0.2.0) - 2017-12-03
### Added
- This release brings us up to date both with PHPUnit and with PHP itself. The minimum version of PHP now supported by the project is **7.0**.
- **Please note:** As with the previously-tagged release, this is not necessarily a stable release!

## [0.2.0](https://github.com/10up/wp_mock/compare/0.2.0...0.1.1) - 2017-07-18
### Added
- **Unstable Distributable Release**
- This release moves to using static, tagged versions hosted on Packagist. Aside from a handful of bugfixes, it's equivalent to the `dev-dev` version many have been using before July 2017. Moving forward, all versions will be static and tagged.
- **Please note:** As with the previously-tagged release, this is not a stable release! We strongly encourage you to use the 1.0.x-dev source release until we release a stable 1.0 version.

## [0.1.1](https://github.com/10up/wp_mock/compare/0.1.1...0.1.0) - 2015-03-31
### Added
- Better documentation and phpDocumentor output

## [0.1.0](https://github.com/10up/wp_mock/commit/3529a7bcc79d196b2850d15b92b94153b0b871a4) - 2014-12-30
### Added
- **Unstable Distributable Release**
- Currently, we only have source releases on Packagist (dev-trunk and 1.0.x-dev). This will be a distributable release that will allow local caching of the library so that not every use needs to be a git clone.
- **Please note:** this is not a stable release! We strongly encourage you to use the 1.0.x-dev source release until we release a stable 1.0 version.

[Unreleased]: https://github.com/10up/wp_mock/compare/trunk...develop
[0.4.2]: https://github.com/10up/wp_mock/compare/0.4.1...0.4.2
[0.4.1]: https://github.com/10up/wp_mock/compare/0.4.0...0.4.1
[0.4.0]: https://github.com/10up/wp_mock/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/10up/wp_mock/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/10up/wp_mock/compare/0.1.1...0.2.0
[0.1.1]: https://github.com/10up/wp_mock/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/10up/wp_mock/commit/3529a7bcc79d196b2850d15b92b94153b0b871a4
