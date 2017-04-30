<?php

namespace AppBundle\Model;

use AppBundle\Model\Condition\Condition;
use AppBundle\Model\Constraint\Constraint;

class RequiredInfo
{
    /**
     * @var Info
     */
    protected $info;

    /**
     * @var Constraint[]
     */
    protected $constraints = [];

    /**
     * @var Condition[]
     */
    protected $conditions = [];

    /**
     * RequiredData constructor.
     * @param Info $info
     * @param Constraint[] $constraints
     * @param Condition[] $conditions
     */
    public function __construct($info = null, $constraints = [], $conditions = [])
    {
        $this->$info = $info;
        $this->constraints = $constraints;
        $this->conditions = $conditions;
    }

    /**
     * @return RequiredInfo
     */
    public static function getOne()
    {
        return new RequiredInfo();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function matches($user)
    {
        $infos = [];
        foreach ($this->info->getKeys() as $key) {
            $infos[$key] = $user->getInfo($key);
        }
        foreach ($this->constraints as $constraint) {
            //echo $this->getInfo()->getId() . " - " . get_class($constraint) . " - " . var_export($constraint->guard, true) . "\n";
            if (!$constraint->matches($this->info->getKeys(), $infos)) return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isEvaluable($user)
    {
        foreach ($this->info->getKeys() as $key) {
            if (!$user->hasInfo($key)) return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function shouldBeCalled($user)
    {
        $infos = [];
        foreach ($this->info->getKeys() as $key) {
            $infos[$key] = $user->getInfo($key);
        }

        foreach ($this->conditions as $condition) {
            if (!$condition->matches($user)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Info
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param Info $info
     * @return RequiredInfo
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param Constraint[] $constraints
     * @return RequiredInfo
     */
    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;
        return $this;
    }

    /**
     * @param Constraint $constraint
     * @return RequiredInfo
     */
    public function addConstraint($constraint)
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param Condition[] $conditions
     * @return RequiredInfo
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @param Condition $condition
     * @return RequiredInfo
     */
    public function addCondition($condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }
}