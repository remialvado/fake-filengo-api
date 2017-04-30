<?php

namespace AppBundle\Model;

class FinancialService implements \JsonSerializable
{
    const TYPE_FINANCIAL_HELP = "financial-help";
    const TYPE_GOOD_PLAN      = "good-plan";

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var RequiredInfo[]
     */
    protected $requiredInfos = [];

    /**
     * FinancialSupport constructor.
     * @param string $name
     * @param string $type
     * @param RequiredInfo[] $requiredInfos
     */
    public function __construct($name, $type, $requiredInfos = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->requiredInfos = $requiredInfos;
    }

    function jsonSerialize()
    {
        return [
            "name" => $this->name,
            "type" => $this->type
        ];
    }

    /**
     * @param string $name
     * @param string $type
     * @param RequiredInfo[] $requiredInfos
     * @return FinancialService
     */
    public static function getOne($name, $type, $requiredInfos = [])
    {
        return new FinancialService($name, $type, $requiredInfos);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function matchesPossibly($user)
    {
        foreach ($this->requiredInfos as $requiredInfo) {
            /*echo $requiredInfo->getInfo()->getId() . "\n";
            echo "   -> should be called : " . ($requiredInfo->shouldBeCalled($user) ? "yes" : "no") . "\n";
            echo "   -> is evaluable : " . ($requiredInfo->isEvaluable($user) ? "yes" : "no") . "\n";
            echo "   -> matches : " . ($requiredInfo->matches($user) ? "yes" : "no") . "\n";
            echo "#######################################\n";*/
            if ($requiredInfo->shouldBeCalled($user) && $requiredInfo->isEvaluable($user) && !$requiredInfo->matches($user)) return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function matchesDefinitely($user)
    {
        foreach ($this->requiredInfos as $requiredInfo) {
            if ($requiredInfo->shouldBeCalled($user) && !$requiredInfo->isEvaluable($user) || !$requiredInfo->matches($user)) return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @return Info[]
     */
    public function getMissingInfos($user)
    {
        $missingInfos = [];
        foreach ($this->requiredInfos as $requiredInfo) {
            if (!$requiredInfo->isEvaluable($user) && $requiredInfo->shouldBeCalled($user)) {
                $missingInfos[$requiredInfo->getInfo()->getId()] = $requiredInfo->getInfo();
            }
        }

        return $missingInfos;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return FinancialService
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return RequiredInfo[]
     */
    public function getRequiredInfos()
    {
        return $this->requiredInfos;
    }

    /**
     * @param RequiredInfo[] $requiredInfos
     * @return FinancialService
     */
    public function setRequiredInfos($requiredInfos)
    {
        $this->requiredInfos = $requiredInfos;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return FinancialService
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}