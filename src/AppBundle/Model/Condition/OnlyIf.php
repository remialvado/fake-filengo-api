<?php

namespace AppBundle\Model\Condition;

class OnlyIf extends AbstractCondition
{
    public function matches($user)
    {
        return $user->hasInfo($this->key) && $this->constraint->matches([$this->key => "value"], [$user->getInfo($this->key)]);
    }
}