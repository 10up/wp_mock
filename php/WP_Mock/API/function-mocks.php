<?php

use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock\Functions\Handler;

if (! function_exists('add_action')) {
    /**
     * Hooks a function on to a specific action.
     *
     * Actions are the hooks that WordPress launches at specific points during execution, or when specific events occur.
     * Plugins can specify that one or more of its PHP functions are executed at these points, using the Action API.
     *
     * @link https://developer.wordpress.org/plugins/hooks/actions/
     *
     * @param string $tag the name of the action to which the $function_to_add is hooked
     * @param string|callable-string|callable $functionToAdd the name of the function you wish to be called
     * @param int $priority optional, used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action
     * @param int $acceptedArgs the number of arguments the function accept (default 1)
     */
    function add_action(string $tag, $functionToAdd, int $priority = 10, int $acceptedArgs = 1)
    {
        \WP_Mock::onActionAdded($tag)->react($functionToAdd, $priority, $acceptedArgs);
    }
}

if (! function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action hook.
     *
     * @param string $tag     The name of the action to be executed.
     * @param mixed  $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
     *
     * @return null Will return null if $tag does not exist in $wp_filter array
     */
    function do_action($tag, $arg = '')
    {
        $args = func_get_args();
        $args = array_slice($args, 1);

        return \WP_Mock::onAction($tag)->react($args);
    }
}

if (! function_exists('add_filter')) {
    /**
     * Hooks a function on to a specific filter.
     *
     * Filters are the hooks that WordPress uses to alter the value of a variable at specific points during execution.
     * Plugins can specify that one or more of its PHP functions are executed at these points, using the Filter API, to change the value of that variable.
     *
     * @link https://developer.wordpress.org/plugins/hooks/filters/
     *
     * @param string $tag the name of the action to which the $function_to_add is hooked
     * @param string|callable-string|callable $functionToAdd the name of the function you wish to be called
     * @param int $priority optional, used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action
     * @param int $acceptedArgs the number of arguments the function accept (default 1)
     */
    function add_filter(string $tag, $functionToAdd, int $priority = 10, int $acceptedArgs = 1)
    {
        \WP_Mock::onFilterAdded($tag)->react($functionToAdd, $priority, $acceptedArgs);
    }
}

if (! function_exists('apply_filters')) {
    /**
     * Call the functions added to a filter hook.
     *
     * @param string $tag     The name of the filter hook.
     * @param mixed  $value   The value on which the filters hooked to <tt>$tag</tt> are applied on.
     * @param mixed  $var,... Additional variables passed to the functions hooked to <tt>$tag</tt>.
     *
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    function apply_filters($tag, $value)
    {
        $args    = func_get_args();
        $args    = array_slice($args, 1);
        $args[0] = $value;

        return \WP_Mock::onFilter($tag)->apply($args);
    }
}

if (! function_exists('esc_html')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_html()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_attr')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_attr()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_url')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_url()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_url_raw')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_url_raw()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_js')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_js()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_textarea')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_textarea()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('__')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function __()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('_e')) {
    /**
     * @return void
     * @throws ExpectationFailedException|Exception
     */
    function _e(): void
    {
        Handler::handlePredefinedEchoFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('_x')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function _x()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_html__')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_html__()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_html_e')) {
    /**
     * @return void
     * @throws ExpectationFailedException|Exception
     */
    function esc_html_e(): void
    {
        Handler::handlePredefinedEchoFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_html_x')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_html_x()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_attr__')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException|Exception
     */
    function esc_attr__()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_attr_e')) {
    /**
     * @return void
     * @throws ExpectationFailedException|Exception
     */
    function esc_attr_e(): void
    {
        Handler::handlePredefinedEchoFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('esc_attr_x')) {
    /**
     * @return string|mixed
     * @throws ExpectationFailedException
     */
    function esc_attr_x()
    {
        return Handler::handlePredefinedReturnFunction(__FUNCTION__, func_get_args());
    }
}

if (! function_exists('_n')) {
    /**
     * @return string|mixed singular or plural string based on number
     * @throws ExpectationFailedException if too few arguments passed
     */
    function _n()
    {
        $args = func_get_args();

        if (count($args) >= 3) {
            /** @phpstan-ignore-next-line */
            if (isset($args[0]) && 1 >= intval($args[2])) {
                return $args[0];
            } else {
                return $args[1];
            }
        } else {
            throw new ExpectationFailedException(sprintf('Too few arguments to function %s', __FUNCTION__));
        }
    }
}
