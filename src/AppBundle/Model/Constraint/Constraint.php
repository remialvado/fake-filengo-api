<?php

namespace AppBundle\Model\Constraint;

interface Constraint
{
    /**
     * @param array $keys
     * @param array $values
     * @return bool
     */
    public function matches($keys, $values);
}