<?php

namespace WP_Mock\Functions;

use WP_Mock\Functions;

/**
 * Object representation of a return sequence of arguments of a function.
 *
 * @see Functions::setExpectedReturn()
 * @see Functions::parseExpectedReturn()
 */
class ReturnSequence
{
    /** @var array<mixed> */
    private array $returnValues;

    /**
     * Constructor to set up the return sequence object.
     */
    public function __construct()
    {
        $this->returnValues = func_get_args();
    }

    /**
     * Retrieve the $return_values array
     *
     * @return array<mixed>
     */
    public function getReturnValues(): array
    {
        return $this->returnValues;
    }

    /**
     * Set the return values.
     *
     * Values should be passed in as one array. Keys will be discarded.
     *
     * @param array<mixed>|mixed $returnValues
     */
    public function setReturnValues($returnValues): ReturnSequence
    {
        $this->returnValues = array_values((array) $returnValues);

        return $this;
    }
}
