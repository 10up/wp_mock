# Mocking WordPress Constants

Certain constants need to be mocked, otherwise various WordPress functions will attempt to include files that just don't exist.

For example, nearly all uses of the `WP_Http` API require first including:

```
ABSPATH . WPINC . '/class-http.php'
```

If these constants are not set, and files do not exist at the location they specify, functions referencing them will produce a fatal error.

By default, WP_Mock will [mock the following constants](./php/WP_Mock/API/constant-mocks.php):

| Constant         | Default mocked value                   |
|------------------|----------------------------------------|
| `WP_CONTENT_DIR` | `__DIR__ . '/dummy-files'`             |
| `ABSPATH`        | `''`                                   |
| `WPINC`          | `__DIR__ . '/dummy-files/wp-includes'` |
| `EZSQL_VERSION`  | `'WP1.25'`                             |
| `OBJECT`         | `'OBJECT'`                             |
| `Object`         | `'OBJECT'`                             |
| `object`         | `'OBJECT'`                             |
| `OBJECT_K`       | `'OBJECT_K'`                           |
| `ARRAY_A`        | `'ARRAY_A'`                            |
| `ARRAY_N`        | `'ARRAY_N'`                            |

WP_Mock provides a few dummy files, located in the `./php/WP_Mock/API/dummy-files/` directory. These files are used to mock the `WP_CONTENT_DIR` and `WPINC` constants, as shown in the table above.

The `! defined` check is used for all constants, so that individual test environments can override the normal default by setting constants in a bootstrap configuration file.