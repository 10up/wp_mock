<?php

/**
 * @param array<mixed> $arg
 * @return mixed
 */
function wpMockTestReturnFunction(...$arg)
{
    return current(func_get_args());
}

/**
 * @param scalar[] $arg
 * @return void
 */
function wpMockTestEchoFunction(...$arg)
{
    echo current(func_get_args()); // @phpstan-ignore-line
}
