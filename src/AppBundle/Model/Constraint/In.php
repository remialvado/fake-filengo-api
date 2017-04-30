<?php

namespace AppBundle\Model\Constraint;

class In extends BinaryConstraint
{
    public function matches($keys, $values)
    {
        return array_key_exists("value", $keys) && in_array($values[$keys["value"]], $this->guard);
    }
}