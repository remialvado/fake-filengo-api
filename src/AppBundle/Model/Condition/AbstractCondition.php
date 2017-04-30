<?php

namespace AppBundle\Model\Condition;

use AppBundle\Model\Constraint\Constraint;

abstract class AbstractCondition implements Condition
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var Constraint
     */
    protected $constraint;

    /**
     * @param string $key
     * @param Constraint $constraint
     */
    public function __construct($key, $constraint)
    {
        $this->key = $key;
        $this->constraint = $constraint;
    }
}