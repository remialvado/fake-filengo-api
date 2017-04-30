<?php

namespace AppBundle\Model\Constraint;

abstract class BinaryConstraint implements Constraint
{
    /**
     * @var mixed
     */
    public $guard;

    /**
     * BinaryConstraint constructor.
     * @param mixed $guard
     */
    public function __construct($guard)
    {
        $this->guard = $guard;
    }
}