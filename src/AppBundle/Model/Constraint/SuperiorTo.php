<?php

namespace AppBundle\Model\Constraint;

class SuperiorTo extends BinaryConstraint
{
    public function matches($keys, $values)
    {
        return array_key_exists("value", $keys) && $values[$keys["value"]] >= $this->guard;
    }
}