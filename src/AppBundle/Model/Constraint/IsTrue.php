<?php

namespace AppBundle\Model\Constraint;

class IsTrue implements Constraint
{
    public function matches($keys, $values)
    {
        return array_key_exists("value", $keys) && $values[$keys["value"]] === true;
    }
}