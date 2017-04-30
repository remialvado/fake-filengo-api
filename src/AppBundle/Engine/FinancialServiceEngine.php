<?php

namespace AppBundle\Engine;

use JMS\DiExtraBundle\Annotation as DI;
use AppBundle\Model\Condition\OnlyIf;
use AppBundle\Model\Condition\Unless;
use AppBundle\Model\Constraint\AgeInferiorTo;
use AppBundle\Model\Constraint\AgeSuperiorTo;
use AppBundle\Model\Constraint\Any;
use AppBundle\Model\Constraint\EqualTo;
use AppBundle\Model\Constraint\In;
use AppBundle\Model\Constraint\IsTrue;
use AppBundle\Model\Constraint\Not;
use AppBundle\Model\Constraint\SuperiorTo;
use AppBundle\Model\FinancialService;
use AppBundle\Model\Info;
use AppBundle\Model\RequiredInfo;
use AppBundle\Model\User;

/**
 * @DI\Service("filengo.financial.service.engine")
 */
class FinancialServiceEngine
{
    /**
     * @param User $user
     * @return Info
     */
    public function getNextRequiredInfo($user)
    {
        $financialServices = $this->getPossiblyMatchingFinancialServices($user);

        $infos = [];
        foreach ($financialServices as $financialService) {
            $infos = array_merge($infos, $financialService->getMissingInfos($user));
        }
        /*foreach ($infos as $info) {
            echo $info->getId() . "\n";
        }*/

        if (empty($infos)) return null;


        $infos = array_values($infos);
        usort($infos, function(Info $infoA, Info $infoB) {
            if ($infoA->getGroup() === $infoB->getGroup()) return $infoB->getPriority() - $infoA->getPriority();
            if ($infoA->getGroup() === Info::GROUP_PERSONAL_INFOS) return -1;
            if ($infoB->getGroup() === Info::GROUP_PERSONAL_INFOS) return 1;
            if ($infoA->getGroup() === Info::GROUP_EDUCATION) return -1;
            if ($infoB->getGroup() === Info::GROUP_EDUCATION) return 1;
            if ($infoA->getGroup() === Info::GROUP_HOUSING) return -1;
            if ($infoB->getGroup() === Info::GROUP_HOUSING) return 1;
            if ($infoA->getGroup() === Info::GROUP_RESOURCES) return -1;
            if ($infoB->getGroup() === Info::GROUP_RESOURCES) return 1;
            return 0; // obviously, group is not set properly...
        });
        return $infos[0];
    }

    /**
     * @param User $user
     * @return int
     */
    public function countPossiblyMatchingFinancialServices($user)
    {
        return count($this->getPossiblyMatchingFinancialServices($user));
    }

    /**
     * @param User $user
     * @return FinancialService[]
     */
    public function getPossiblyMatchingFinancialServices($user)
    {
        $financialServices = [];
        foreach ($this->financialServices as $financialService) {
            /*echo "œœœœœœœœœœœœœœœœ\n";
            echo $financialService->getName() . "\n";
            echo "œœœœœœœœœœœœœœœœ\n";*/
            if ($financialService->matchesPossibly($user)) $financialServices[] = $financialService;
        }

        return $financialServices;
    }

    /**
     * @param User $user
     * @return int
     */
    public function countDefinitelyMatchingFinancialServices($user)
    {
        return count($this->getDefinitelyMatchingFinancialServices($user));
    }

    /**
     * @param User $user
     * @return FinancialService[]
     */
    public function getDefinitelyMatchingFinancialServices($user)
    {
        $financialServices = [];
        foreach ($this->financialServices as $financialService) {
            if ($financialService->matchesDefinitely($user)) $financialServices[] = $financialService;
        }

        return $financialServices;
    }

    /**
     * FinancialServiceEngine constructor.
     */
    public function __construct()
    {
        $this->initInfos();
        $this->initFinancialServices();
        $previousInfo = null;
    }

    /**
     * @var Info[]
     */
    protected $infos;

    /**
     * @var FinancialService[]
     */
    protected $financialServices;

    protected function initInfos()
    {
        // Personal info
        $this->addInfo(
            Info::getOne("birthday")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_DATE)
                ->setPriority(1000)
        );
        $this->addInfo(
            Info::getOne("nationality")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("collection", "nationality")
                ->setPriority(990)
        );
        $this->addInfo(
            Info::getOne("status")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "Collégien",
                    "Lycéen",
                    "Etudiant",
                    "Apprenti",
                    "Contrat pro"
                ])
                ->setPriority(980)
        );
        $this->addInfo(
            Info::getOne("location.birth")
                ->setKeys([]) // reset
                ->addKey("location.birth.city", "city")
                ->addKey("location.birth.postcode", "postcode")
                ->addKey("location.birth.department", "department")
                ->addKey("location.birth.region", "region")
                ->addKey("location.birth.country", "country")
                ->addKey("location.birth.latitude", "latitude")
                ->addKey("location.birth.longitude", "longitude")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_GEOLOC)
                ->setPriority(970)
        );
        $this->addInfo(
            Info::getOne("location.current")
                ->setKeys([]) // reset
                ->addKey("location.current.city", "city")
                ->addKey("location.current.postcode", "postcode")
                ->addKey("location.current.department", "department")
                ->addKey("location.current.region", "region")
                ->addKey("location.current.country", "country")
                ->addKey("location.current.latitude", "latitude")
                ->addKey("location.current.longitude", "longitude")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_GEOLOC)
                ->setPriority(960)
        );
        $this->addInfo(
            Info::getOne("resident.since")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_INTEGER)
                ->setPriority(950)
        );
        $this->addInfo(
            Info::getOne("family.parent.marital.situation")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "Mariés ou pacsés",
                    "Concubins",
                    "Dovircés",
                    "Séparés",
                    "Veuf ou veuve",
                    "Décédés"
                ])
                ->setPriority(940)
        );
        $this->addInfo(
            Info::getOne("family.siblings.count")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "0",
                    "1",
                    "2",
                    "3",
                    "4",
                    "5",
                    "6 ou plus"
                ])
                ->setPriority(930)
        );
        $this->addInfo(
            Info::getOne("family.children.own")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_CHOICE)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(920)
        );
        $this->addInfo(
            Info::getOne("family.children.support")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_CHOICE)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(910)
        );
        $this->addInfo(
            Info::getOne("wish.licence.car")
                ->setGroup(Info::GROUP_PERSONAL_INFOS)
                ->setType(Info::TYPE_CHOICE)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(900)
        );

        // Education
        $this->addInfo(
            Info::getOne("education.diploma.bac")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(1000)
        );
        $this->addInfo(
            Info::getOne("education.diploma.bac.mention")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "Sans mention",
                    "Mention assez bien",
                    "Mention bien",
                    "Mention très bien"
                ])
                ->setPriority(990)
        );
        $this->addInfo(
            Info::getOne("education.diploma.current")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("collection", "diploma")
                ->setPriority(980)
        );
        $this->addInfo(
            Info::getOne("education.diploma.teaching")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(970)
        );
        $this->addInfo(
            Info::getOne("education.diploma.bac.after")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "Je n'ai pas encore commencé d'études supérieures",
                    "1ère année",
                    "2ème année",
                    "3ème année",
                    "4ème année"
                ])
                ->setPriority(960)
        );
        $this->addInfo(
            Info::getOne("location.education.current")
                ->setKeys([]) // reset
                ->addKey("location.education.current.city", "city")
                ->addKey("location.education.current.postcode", "postcode")
                ->addKey("location.education.current.department", "department")
                ->addKey("location.education.current.region", "region")
                ->addKey("location.education.current.country", "country")
                ->addKey("location.education.current.latitude", "latitude")
                ->addKey("location.education.current.longitude", "longitude")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_GEOLOC)
                ->setPriority(950)
        );
        $this->addInfo(
            Info::getOne("education.school.name")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("collection", "school_name")
                ->setPriority(940)
        );
        $this->addInfo(
            Info::getOne("education.distance.school.parents")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_INTEGER)
                ->setPriority(930)
        );
        $this->addInfo(
            Info::getOne("education.internship.abroad")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(920)
        );
        $this->addInfo(
            Info::getOne("education.internship.abroad.country.isplanned")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(910)
        );
        $this->addInfo(
            Info::getOne("education.internship.abroad.country")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("collection", "country")
                ->setPriority(900)
        );
        $this->addInfo(
            Info::getOne("education.internship.abroad.duration")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_INTEGER)
                ->setPriority(890)
        );
        $this->addInfo(
            Info::getOne("education.internship.abroad.convention")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "Oui",
                    "Non",
                    "Je ne sais pas"
                ])
                ->setPriority(880)
        );
        $this->addInfo(
            Info::getOne("education.study.abroad")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(870)
        );
        $this->addInfo(
            Info::getOne("education.study.abroad.country.isplanned")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(860)
        );
        $this->addInfo(
            Info::getOne("education.study.abroad.country")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("collection", "country")
                ->setPriority(850)
        );
        $this->addInfo(
            Info::getOne("education.study.abroad.duration")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_INTEGER)
                ->setPriority(840)
        );
        $this->addInfo(
            Info::getOne("education.study.abroad.convention")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "Oui",
                    "Non",
                    "Je ne sais pas"
                ])
                ->setPriority(830)
        );

        // housing
        $this->addInfo(
            Info::getOne("housing.me.type")
                ->setGroup(Info::GROUP_HOUSING)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("values", [
                    "Locataire (mon nom figure sur le bail)",
                    "Propriétaire",
                    "Hébergé (chez tes parents ou toute autre personne)"
                ])
                ->setPriority(1000)
        );
        $this->addInfo(
            Info::getOne("housing.me.family.link")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(990)
        );
        $this->addInfo(
            Info::getOne("housing.me.status")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("value", [
                    "Seul",
                    "En couple",
                    "En colocation"
                ])
                ->setPriority(980)
        );
        $this->addInfo(
            Info::getOne("housing.flat.type")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("value", [
                    "Studio / Appartement / Maison",
                    "Chambre chez un particulier ou à l'hôtel",
                    "Résidence CROUS"
                ])
                ->setPriority(970)
        );
        $this->addInfo(
            Info::getOne("housing.flat.furniture")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_CHOICE)
                ->setOption("value", [
                    "Meublé",
                    "Non meublé"
                ])
                ->setPriority(960)
        );
        $this->addInfo(
            Info::getOne("housing.rent.base.alone")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(950)
        );
        $this->addInfo(
            Info::getOne("housing.rent.base.own")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(949)
        );
        $this->addInfo(
            Info::getOne("housing.rent.base.group")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(948)
        );
        $this->addInfo(
            Info::getOne("housing.rent.extra")
                ->setGroup(Info::GROUP_EDUCATION)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(940)
        );

        // resources
        $this->addInfo(
            Info::getOne("resources.income")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(1000)
        );
        $this->addInfo(
            Info::getOne("resources.income.monthly")
                ->setKeys([]) // reset
                ->addKey("resources.income.monthly.m1", "value-1")
                ->addKey("resources.income.monthly.m2", "value-2")
                ->addKey("resources.income.monthly.m3", "value-3")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(990)
        );
        $this->addInfo(
            Info::getOne("resources.tax.own.has")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(980)
        );
        $this->addInfo(
            Info::getOne("resources.tax.parents.has")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(970)
        );
        $this->addInfo(
            Info::getOne("resources.tax.own.brut")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(960)
        );
        $this->addInfo(
            Info::getOne("resources.tax.parents.brut")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(950)
        );
        $this->addInfo(
            Info::getOne("resources.tax.parents.reference")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_FLOAT)
                ->setPriority(940)
        );
        $this->addInfo(
            Info::getOne("resources.tax.siblings")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_INTEGER)
                ->setPriority(930)
        );
        $this->addInfo(
            Info::getOne("resources.tax.siblings.superior.school")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_INTEGER)
                ->setPriority(920)
        );
        $this->addInfo(
            Info::getOne("wish.student.loan.personal")
                ->setGroup(Info::GROUP_RESOURCES)
                ->setType(Info::TYPE_BOOLEAN)
                ->setPriority(910)
        );

    }

    /**
     * @param Info $info
     */
    protected function addInfo($info)
    {
        $this->infos[$info->getId()] = $info;
    }

    /**
     * @param string $id
     * @return Info
     * @throws \Exception
     */
    protected function getInfo($id)
    {
        if (!array_key_exists($id, $this->infos)) {
            throw new \Exception("missing info with id '$id");
        }
        return $this->infos[$id];
    }

    /**
     * Should be read from mongo but this is just a fake API!
     */
    protected function initFinancialServices()
    {
        $this->financialServices = [
            FinancialService::getOne("CROUS", FinancialService::TYPE_FINANCIAL_HELP, [
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("birthday"))
                    ->addConstraint(new AgeSuperiorTo(18))
                    ->addConstraint(new AgeInferiorTo(25)),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("nationality"))
                    ->addConstraint(new EqualTo("française")),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("status"))
                    ->addConstraint(new In(["Etudiant", "Apprenti", "Contrat pro"])),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.birth"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.current"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resident.since"))
                    ->addCondition(new OnlyIf("nationality", new Not(new EqualTo("française"))))
                    ->addConstraint(new SuperiorTo(12)),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.parent.marital.situation"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.siblings.count"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.children.own"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.children.support"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("wish.licence.car"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.diploma.bac"))
                    ->addConstraint(new IsTrue()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.diploma.bac.mention"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.diploma.current"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.diploma.bac.after"))
                    ->addConstraint(new Not(new EqualTo("Je n'ai pas encore commencé d'études supérieures"))),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.education.current"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.school.name"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.distance.school.parents"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.country.isplanned"))
                    ->addCondition(new OnlyIf("education.internship.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.country"))
                    ->addCondition(new OnlyIf("education.internship.abroad.country.isplanned", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.duration"))
                    ->addCondition(new OnlyIf("education.internship.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.convention"))
                    ->addCondition(new OnlyIf("education.internship.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.country.isplanned"))
                    ->addCondition(new OnlyIf("education.study.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.country"))
                    ->addCondition(new OnlyIf("education.study.abroad.country.isplanned", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.duration"))
                    ->addCondition(new OnlyIf("education.study.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.convention"))
                    ->addCondition(new OnlyIf("education.study.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.me.type"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.me.family.link"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.me.status"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.flat.type"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.flat.furniture"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.rent.base.alone"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addCondition(new OnlyIf("housing.me.status", new EqualTo("Seul")))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.rent.base.group"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addCondition(new OnlyIf("housing.me.status", new Not(new EqualTo("Seul"))))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.rent.base.own"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addCondition(new OnlyIf("housing.me.status", new EqualTo("En couple")))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("housing.rent.extra"))
                    ->addCondition(new OnlyIf("housing.me.type", new EqualTo("Locataire (mon nom figure sur le bail)")))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.income"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.income.monthly"))
                    ->addCondition(new OnlyIf("resources.income", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.tax.own.has"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.tax.parents.has"))
                    ->addCondition(new Unless("resources.tax.own.has", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.tax.own.brut"))
                    ->addCondition(new OnlyIf("resources.tax.own.has", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.tax.parents.brut"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.tax.parents.reference"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.tax.siblings"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resources.tax.siblings.superior.school"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("wish.student.loan.personal"))
                    ->addConstraint(new Any()),
            ]),
            FinancialService::getOne("Erasmus+ Stage", FinancialService::TYPE_FINANCIAL_HELP, [
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("birthday"))
                    ->addConstraint(new AgeSuperiorTo(18))
                    ->addConstraint(new AgeInferiorTo(25)),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("nationality"))
                    ->addConstraint(new EqualTo("française")),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("status"))
                    ->addConstraint(new In(["Etudiant", "Apprenti", "Contrat pro"])),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.birth"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.current"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resident.since"))
                    ->addCondition(new OnlyIf("nationality", new Not(new EqualTo("française"))))
                    ->addConstraint(new SuperiorTo(12)),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.parent.marital.situation"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.siblings.count"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.children.own"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.children.support"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("wish.licence.car"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.diploma.bac"))
                    ->addConstraint(new IsTrue()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.country.isplanned"))
                    ->addCondition(new OnlyIf("education.internship.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.country"))
                    ->addCondition(new OnlyIf("education.internship.abroad.country.isplanned", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.duration"))
                    ->addCondition(new OnlyIf("education.internship.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.internship.abroad.convention"))
                    ->addCondition(new OnlyIf("education.internship.abroad", new IsTrue()))
                    ->addConstraint(new Any())
            ]),
            FinancialService::getOne("Erasmus+ Etudes", FinancialService::TYPE_FINANCIAL_HELP, [
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("birthday"))
                    ->addConstraint(new AgeSuperiorTo(18))
                    ->addConstraint(new AgeInferiorTo(25)),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("nationality"))
                    ->addConstraint(new EqualTo("française")),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("status"))
                    ->addConstraint(new In(["Etudiant", "Apprenti", "Contrat pro"])),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.birth"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.current"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("resident.since"))
                    ->addCondition(new OnlyIf("nationality", new Not(new EqualTo("française"))))
                    ->addConstraint(new SuperiorTo(12)),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.parent.marital.situation"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.siblings.count"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.children.own"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("family.children.support"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("wish.licence.car"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.diploma.bac"))
                    ->addConstraint(new IsTrue()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.country.isplanned"))
                    ->addCondition(new OnlyIf("education.study.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.country"))
                    ->addCondition(new OnlyIf("education.study.abroad.country.isplanned", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.duration"))
                    ->addCondition(new OnlyIf("education.study.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.study.abroad.convention"))
                    ->addCondition(new OnlyIf("education.study.abroad", new IsTrue()))
                    ->addConstraint(new Any()),
            ]),
            FinancialService::getOne("Emploi d'avenir professeur", FinancialService::TYPE_FINANCIAL_HELP, [
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("birthday"))
                    ->addConstraint(new AgeSuperiorTo(18))
                    ->addConstraint(new AgeInferiorTo(25)),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("nationality"))
                    ->addConstraint(new EqualTo("française")),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("status"))
                    ->addConstraint(new In(["Etudiant"])),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("location.current"))
                    ->addConstraint(new Any()),
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("education.diploma.teaching"))
                    ->addConstraint(new IsTrue()),
            ]),
            FinancialService::getOne("Yestudent", FinancialService::TYPE_GOOD_PLAN, [
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("status"))
                    ->addConstraint(new In(["Lycéen", "Etudiant", "Apprenti", "Contrat pro"])),
            ]),
            FinancialService::getOne("Ornikar", FinancialService::TYPE_GOOD_PLAN, [
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("birthday"))
                    ->addConstraint(new AgeSuperiorTo(16))
            ]),
            FinancialService::getOne("Carte 12-27", FinancialService::TYPE_GOOD_PLAN, [
                RequiredInfo::getOne()
                    ->setInfo($this->getInfo("birthday"))
                    ->addConstraint(new AgeSuperiorTo(12))
                    ->addConstraint(new AgeInferiorTo(27))
            ])
        ];
    }
}