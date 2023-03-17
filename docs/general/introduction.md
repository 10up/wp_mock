# Introduction

## What is WP_Mock

[WP_Mock](https://github.com/10up/wp_mock) is a unit test tool for PHP projects that extend or build upon , such as plugins, themes, or even whole websites.

WP_Mock helps mocking common WordPress functions and components, making it easier to write unit tests for your project. It also helps perform additional assertions over your code that are not part of the standard toolset.

When writing code for WordPress, it so often happens that the code you create needs to invoke a WordPress function, class or hook, which is external code you are integrating your project with. Ideally, though, a unit test will not depend on WordPress being loaded in order to test your code. By constructing mocks, it's possible to simulate WordPress core functionality by defining their expected arguments, responses, the number of times they are called, and more. 