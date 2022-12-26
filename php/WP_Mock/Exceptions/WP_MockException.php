<?php

namespace WP_Mock\Exceptions;

use Exception;
use Throwable;

/**
 * Base WP_Mock Exception.
 */
class WP_MockException extends Exception
{
    /** @var int default from {@see Exception} - child implementations of this class may override this */
    protected $code = 0;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}
