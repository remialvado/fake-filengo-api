<?php

namespace AppBundle\Model\Constraint;

/**
 * Quite handy to define a constraint that always matches. It allows to ask some data without really need them ;)
 */
class Any implements Constraint
{
    public function matches($keys, $values)
    {
        return true;
    }
}