<?php

namespace AppBundle\Model\Constraint;

class Not extends BinaryConstraint
{
    public function matches($keys, $values)
    {
        return !call_user_func_array([$this->guard, "matches"], [$keys, $values]);
    }
}