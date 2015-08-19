# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/10up/wp_mock)

## Branches

* We try to follow [SemVer](http://semver.org/) in WP Mock
* The current minor version lives on the **master** branch. Until a new minor (or major) version is released, the master branch will be aliased to appear as the dev package of the current minor version in Packagist (e.g. if the current minor version is `1.0`, master will be aliased to `1.0.x-dev`).
* The development release lives on the **dev** branch. Until it is officially released, the dev branch will be aliased to appear as the dev package of the next minor version in Packagist (e.g. if the next minor version is `1.2`, the dev branch will be aliased to `1.2.x-dev`).
* Old minor versions will live in their own version branch (e.g. if the current minor version is `1.2`, the `1.1` major version will live in a `1.1` branch

## Pull Requests

* New features must be submitted against the **dev** branch
* Bug fixes should be submitted against the branch in which the bug exists. If the bug exists in multiple releases, please submit the Pull Request against the most recent branch and make a note of which other major versions need the fix (e.g. if the bug exists in all versions, submit against dev; if it no longer exists in dev, submit against master). Please do not open multiple pull requests for the same fix against different branches.
* If you're not sure whether a feature idea would be something we'd be interested in, please open an issue before you start working on it. We'd be happy to discuss your idea with you.

## Tests

We know. We're kind of working on it. Want to start writing them for us? :D

## Thanks

**You're awesome** - Thanks for being interested in contributing your time and code to this project!