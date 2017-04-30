<?php

namespace AppBundle\Model\Constraint;

class AgeSuperiorTo extends BinaryConstraint
{
    public function matches($keys, $values)
    {
        if (!array_key_exists("value", $keys)) return false;
        $birthDay = new \DateTime($values[$keys["value"]]);
        $currentDate = new \DateTime();
        return $birthDay->add(new \DateInterval("P" . $this->guard . "Y")) <= $currentDate;
    }
}