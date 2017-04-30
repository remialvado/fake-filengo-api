<?php

namespace AppBundle\Model\Condition;

use AppBundle\Model\User;

interface Condition
{
    /**
     * @param User $user
     * @return bool
     */
    public function matches($user);
}