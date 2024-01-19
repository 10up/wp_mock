<?php

/**
 * @package WP_Mock
 * @copyright 2013-2024 by the contributors
 * @license BSD-3-Clause
 * @see ../LICENSE.md
 */

use Mockery\Exception as MockeryException;
use WP_Mock\DeprecatedMethodListener;
use WP_Mock\Functions\Handler;
use WP_Mock\Matcher\FuzzyObject;
use Mockery\Matcher\Type;

/**
 * WP_Mock main class.
 */
class WP_Mock
{
    /**
     * @var \WP_Mock\EventManager
     */
    protected static $event_manager;

    /** @var WP_Mock\Functions */
    protected static $functionsManager;

    protected static $__bootstrapped = false;

    protected static $__use_patchwork = false;

    protected static $__strict_mode = false;

    /** @var DeprecatedMethodListener */
    protected static $deprecatedMethodListener;

    /**
     * @param boolean $use_patchwork
     */
    public static function setUsePatchwork($use_patchwork)
    {
        if (! self::$__bootstrapped) {
            self::$__use_patchwork = (bool) $use_patchwork;
        }
    }

    public static function usingPatchwork()
    {
        return (bool) self::$__use_patchwork;
    }

    /**
     * Check whether strict mode is turned on
     *
     * @return bool
     */
    public static function strictMode()
    {
        return (bool) self::$__strict_mode;
    }

    /**
     * Turns on strict mode
     */
    public static function activateStrictMode()
    {
        if (! self::$__bootstrapped) {
            self::$__strict_mode = true;
        }
    }

    /**
     * Bootstraps WP_Mock.
     *
     * @return void
     */
    public static function bootstrap(): void
    {
        if (! self::$__bootstrapped) {
            self::$__bootstrapped = true;

            static::$deprecatedMethodListener = new DeprecatedMethodListener();

            require_once __DIR__ . '/WP_Mock/API/function-mocks.php';
            require_once __DIR__ . '/WP_Mock/API/constant-mocks.php';

            if (self::usingPatchwork()) {
                $patchwork_path  = 'antecedent/patchwork/Patchwork.php';
                $possible_locations = [
                    'vendor',
                    '../..',
                ];

                foreach ($possible_locations as $loc) {
                    $path = __DIR__ . "/../$loc/$patchwork_path";

                    if (file_exists($path)) {
                        break;
                    }
                }

                // Will cause a fatal error if patchwork can't be found
                require_once($path);
            }

            self::setUp();
        }
    }

    /**
     * Make sure Mockery doesn't have anything set up already.
     */
    public static function setUp(): void
    {
        if (self::$__bootstrapped) {
            \Mockery::close();

            self::$event_manager    = new \WP_Mock\EventManager();
            self::$functionsManager = new \WP_Mock\Functions();
        } else {
            self::bootstrap();
        }
    }

    /**
     * Tear down anything built up inside Mockery when we're ready to do so.
     */
    public static function tearDown(): void
    {
        self::$event_manager->flush();
        self::$functionsManager->flush();

        Mockery::close();
        Handler::cleanup();
    }

    /**
     * Fire a specific (mocked) callback when an apply_filters() call is used.
     *
     * @param string $filter
     *
     * @return \WP_Mock\Filter
     */
    public static function onFilter($filter)
    {
        self::$event_manager->called($filter, 'filter');
        return self::$event_manager->filter($filter);
    }

    /**
     * Fire a specific (mocked) callback when a do_action() call is used.
     *
     * @param string $action
     *
     * @return \WP_Mock\Action
     */
    public static function onAction($action)
    {
        return self::$event_manager->action($action);
    }

    /**
     * Get a filter or action added callback object
     *
     * @param string $hook
     * @param string $type
     *
     * @return \WP_Mock\HookedCallback
     */
    public static function onHookAdded($hook, $type = 'filter')
    {
        return self::$event_manager->callback($hook, $type);
    }

    /**
     * Get a filter added callback object
     *
     * @param string $hook
     *
     * @return \WP_Mock\HookedCallback
     */
    public static function onFilterAdded($hook)
    {
        return self::onHookAdded($hook, 'filter');
    }

    /**
     * Get an action added callback object
     *
     * @param string $hook
     *
     * @return \WP_Mock\HookedCallback
     */
    public static function onActionAdded($hook)
    {
        return self::onHookAdded($hook, 'action');
    }

    /**
     * Alert the Event Manager that an action has been invoked.
     *
     * @param string $action
     */
    public static function invokeAction($action)
    {
        self::$event_manager->called($action);
    }

    public static function addFilter($hook)
    {
        self::addHook($hook, 'filter');
    }

    public static function addAction($hook)
    {
        self::addHook($hook, 'action');
    }

    public static function addHook($hook, $type = 'filter')
    {
        $type_name = "$type::$hook";
        self::$event_manager->called($type_name, 'callback');
    }

    /**
     * Adds an expectation that an action will be called during the test.
     *
     * @param string $action expected action
     * @returnv oid
     */
    public static function expectAction(string $action) : void
    {
        $intercept = Mockery::mock('intercept');
        $intercept->shouldReceive('intercepted')->atLeast()->once();
        $args = func_get_args();
        $args = count($args) > 1 ? array_slice($args, 1) : array( null );

        $mocked_action = self::onAction($action);
        $responder     = call_user_func_array(array( $mocked_action, 'with' ), $args);
        $responder->perform([$intercept, 'intercepted']);
    }

    /**
     * Adds an expectation that a filter will be applied during the test.
     *
     * @param string $filter expected filter
     * @return void
     */
    public static function expectFilter(string $filter) : void
    {
        $intercept = Mockery::mock('intercept');
        $intercept->shouldReceive('intercepted')->atLeast()->once()->andReturnUsing(function ($value) {
            return $value;
        });
        $args = func_num_args() > 1 ? array_slice(func_get_args(), 1) : array( null );

        $mocked_filter = self::onFilter($filter);
        $responder     = call_user_func_array(array( $mocked_filter, 'with' ), $args);
        $responder->reply(new WP_Mock\InvokedFilterValue(array( $intercept, 'intercepted' )));
    }

    /**
     * Asserts that all actions are called.
     *
     * @return void
     */
    public static function assertActionsCalled() : void
    {
        $allActionsCalled = self::$event_manager->allActionsCalled();
        $failed = implode(', ', self::$event_manager->expectedActions());
        PHPUnit\Framework\Assert::assertTrue($allActionsCalled, 'Method failed to invoke actions: ' . $failed);
    }

    /**
     * Asserts that all filters are called.
     *
     * @return void
     */
    public static function assertFiltersCalled() : void
    {
        $allFiltersCalled = self::$event_manager->allFiltersCalled();
        $failed           = implode(', ', self::$event_manager->expectedFilters());
        PHPUnit\Framework\Assert::assertTrue($allFiltersCalled, 'Method failed to invoke filters: ' . $failed);
    }

    /**
     * Adds an expectation that an action hook should be added.
     *
     * @param string $action the action hook name
     * @param string|callable-string|callable|Type $callback the callback that should be registered
     * @param int $priority the priority it should be registered at
     * @param int $args the number of arguments that should be allowed
     * @return void
     */
    public static function expectActionAdded(string $action, $callback, int $priority = 10, int $args = 1) : void
    {
        self::expectHookAdded('action', $action, $callback, $priority, $args);
    }

    /**
     * Adds an expectation that an action hook should not be added.
     *
     * @param string $action the action hook name
     * @param string|callable-string|callable|Type $callback the callback that should be registered
     * @param int $priority the priority it should be registered at
     * @param int $args the number of arguments that should be allowed
     * @return void
     */
    public static function expectActionNotAdded(string $action, $callback, int $priority = 10, int $args = 1) : void
    {
        self::expectHookNotAdded('action', $action, $callback, $priority, $args);
    }

    /**
     * Add an expectation that a filter hook should be added.
     *
     * @param string $filter the filter hook name
     * @param string|callable-string|callable|Type $callback the callback that should be registered
     * @param int $priority the priority it should be registered at
     * @param int $args the number of arguments that should be allowed
     * @return void
     */
    public static function expectFilterAdded(string $filter, $callback, int $priority = 10, int $args = 1) : void
    {
        self::expectHookAdded('filter', $filter, $callback, $priority, $args);
    }

    /**
     * Adds an expectation that a filter hook should not be added.
     *
     * @param string $filter the filter hook name
     * @param string|callable-string|callable|Type $callback the callback that should be registered
     * @param int $priority the priority it should be registered at
     * @param int $args the number of arguments that should be allowed
     * @return void
     */
    public static function expectFilterNotAdded(string $filter, $callback, int $priority = 10, int $args = 10) : void
    {
        self::expectHookNotAdded('filter', $filter, $callback, $priority, $args);
    }

    /**
     * Adds an expectation that a hook should be added.
     *
     * Based {@see Mockery\MockInterface::shouldReceive()}.
     *
     * @param string $type the type of hook being added ('action' or 'filter')
     * @param string $hook the hook name
     * @param string|callable-string|callable|Type $callback the callback that should be registered
     * @param int $priority the priority it should be registered at
     * @param int $args the number of arguments that should be allowed
     * @return void
     */
    public static function expectHookAdded(string $type, string $hook, $callback, int $priority = 10, int $args = 1) : void
    {
        $intercept = Mockery::mock('intercept');
        $intercept->shouldReceive('intercepted')->atLeast()->once();

        /** @var WP_Mock\HookedCallbackResponder $responder */
        $responder = self::onHookAdded($hook, $type)
            ->with($callback, $priority, $args);
        $responder->perform([$intercept, 'intercepted']);
    }

    /**
     * Adds an expectation that a hook should not be added.
     *
     * Based {@see Mockery\MockInterface::shouldNotReceive()}.
     *
     * @param string $type the type of hook being added ('action' or 'filter')
     * @param string $hook the hook name
     * @param string|callable-string|callable|Type $callback the callback that should be registered
     * @param int $priority the priority it should be registered at
     * @param int $args the number of arguments that should be allowed
     * @return void
     */
    public static function expectHookNotAdded(string $type, string $hook, $callback, int $priority = 10, int $args = 1) : void
    {
        $intercept = Mockery::mock('intercept');
        $intercept->shouldNotReceive('intercepted');

        /** @var WP_Mock\HookedCallbackResponder $responder */
        $responder = self::onHookAdded($hook, $type)
            ->with($callback, $priority, $args);
        $responder->perform([$intercept, 'intercepted']);
    }

    /**
     * Asserts that all hooks are added.
     *
     * @return void
     */
    public static function assertHooksAdded() : void
    {
        $allHooksAdded = self::$event_manager->allHooksAdded();
        $failed = implode(', ', self::$event_manager->expectedHooks());
        PHPUnit\Framework\Assert::assertTrue($allHooksAdded, 'Method failed to add hooks: ' . $failed);
    }

    /**
     * Mocks a WordPress API function.
     *
     * This function registers a mock object for a WordPress function and, if necessary, dynamically defines the function.
     *
     * Pass the function name as the first argument (e.g. `wp_remote_get()`) and pass in details about the expectations in the $args param.
     * The arguments have a few options for defining expectations about how the WordPress function should be used during a test.
     *
     * Currently, it accepts the following settings:
     *
     * - `times`: Defines expectations for the number of times a function should be called. The default is `0` or more times.
     *            To expect the function to be called an exact amount of times, set times to a non-negative numeric value.
     *            To specify that the function should be called a minimum number of times, use a string with the minimum followed by '+' (e.g. '3+' means 3 or more times).
     *            Append a '-' to indicate a maximum number of times a function should be called (e.g. '3-' means no more than 3 times).
     *            To indicate a range, use '-' between two numbers (e.g. '2-5' means at least 2 times and no more than 5 times).
     *
     * - `return`: Defines the value (if any) that the function should return.
     *             If you pass a `Closure` as the return value, the function will return whatever the closure's return value.
     *
     * - `return_in_order`: Use this if your function will be called multiple times in the test but needs to have different return values.
     *                      Set this to an array of return values. Each time the function is called, it will return the next value in the sequence until it reaches the last value, which will become the return value for all subsequent calls.
     *                      For example, if you are mocking `is_single()`, you can set `return_in_order` to `[false, true]`. The first time is_single() is called it will return false.
     *                      The second and all subsequent times it will return true. Setting this value overrides return, so if you set both, return will be ignored.
     *
     * - `return_arg`: Use this to specify that the function should return one of its arguments. `return_arg` should be the position of the argument in the arguments array, so `0` for the first argument, `1` for the second, etc.
     *                 You can also set this to true, which is equivalent to `0`. This will override both return and return_in_order.
     *
     * - `args`: Use this to set expectations about what the arguments passed to the function should be.
     *           This value should always be an array with the arguments in order.
     *           Like with `return`, if you use a `Closure`, its return value will be used to validate the argument expectations.
     *           WP_Mock has several helper functions to make this feature more flexible. There are static methods on the \WP_Mock\Functions class. They are:
     *           - {@see Functions::type($type)} Expects an argument of a certain type. This can be any core PHP data type (string, int, resource, callable, etc.) or any class or interface name.
     *           - {@see Functions::anyOf($values)} Expects the argument to be any value in the `$values` array.
     *           In addition to these helper functions, you can indicate that the argument can be any value of any type by using `*`.
     *           So, for example, if you are expecting `get_post_meta()` to be called, the `args` array might look something like this: `[$post->ID, 'some_meta_key', true]`.
     *
     * Returns the {@see Mockery\Expectation} object with the function expectations added.
     * It is possible to use Mockery methods to add expectations to the object returned, which will then be combined with any expectations that may have been passed as arguments.
     *
     * @param string $function function name
     * @param mixed[] $args optional arguments to set expectations
     * @return Mockery\Expectation
     * @throws InvalidArgumentException
     */
    public static function userFunction(string $function, array $args = [])
    {
        return self::$functionsManager->register($function, $args);
    }

    /**
     * A wrapper for {@see WP_Mock::userFunction()} that will simply set/override the return to be a function that echoes the value that its passed.
     *
     * For example, `esc_attr_e()` may need to be mocked, and it must echo some value.
     * {@see WP_Mock::echoFunction()} will set `esc_attr_e()` to echo the value its passed:
     *
     *    WP_Mock::echoFunction('esc_attr_e');
     *    esc_attr_e('some_value'); // echoes "some_value"
     *
     * @param string $function function name
     * @param mixed[]|scalar $args optional arguments
     * @return Mockery\Expectation
     * @throws InvalidArgumentException
     */
    public static function echoFunction(string $function, $args = [])
    {
        /** @var array<string, mixed> $args */
        $args = (array) $args;
        $args['return'] = function ($param) {
            echo $param;
        };

        return self::$functionsManager->register($function, $args);
    }

    /**
     * A wrapper for {@see WP_Mock::userFunction()} that will simply set/override the return to be a function that returns the value that its passed.
     *
     * For example, `esc_attr()` may need to be mocked, and it must return some value.
     * {@see WP_Mock::passthruFunction()} will set `esc_attr()` to return the value its passed:
     *
     *    WP_Mock::passthruFunction('esc_attr');
     *    echo esc_attr('some_value'); // echoes "some_value"
     *
     * @param string $function function name
     * @param mixed[]|scalar $args function arguments (optional)
     * @return Mockery\Expectation
     * @throws InvalidArgumentException
     */
    public static function passthruFunction(string $function, $args = [])
    {
        /** @var array<string, mixed> $args */
        $args = (array) $args;
        $args['return'] = function ($param) {
            return $param;
        };

        return self::$functionsManager->register($function, $args);
    }

    /**
     * Adds a function mock that aliases another callable.
     *
     * e.g.: WP_Mock::alias('wp_hash', 'md5');
     *
     * @param string|callable-string $function function to alias
     * @param string|callable-string $aliasFunction actual function
     * @param mixed[]|scalar $args optional arguments
     * @return Mockery\Expectation
     * @throws InvalidArgumentException
     */
    public static function alias(string $function, string $aliasFunction, $args = [])
    {
        /** @var array<string, mixed> $args */
        $args = (array) $args;

        if (is_callable($aliasFunction)) {
            $args['return'] = function () use ($aliasFunction) {
                return call_user_func_array($aliasFunction, func_get_args());
            };
        }

        return self::$functionsManager->register($function, $args);
    }

    /**
     * Generates a fuzzy object match expectation.
     *
     * This will let you fuzzy match objects based on their properties without needing to use the identical (===) operator.
     * This is helpful when the object being passed to a function is constructed inside the scope of the function being tested but where you want to make assertions on more than just the type of the object.
     *
     * @param object|array<mixed> $object
     * @return FuzzyObject
     * @throws MockeryException
     */
    public static function fuzzyObject($object): FuzzyObject
    {
        return new FuzzyObject($object);
    }

    /**
     * Gets the deprecated method listener instance.
     *
     * @return DeprecatedMethodListener
     */
    public static function getDeprecatedMethodListener(): DeprecatedMethodListener
    {
        return static::$deprecatedMethodListener;
    }
}
