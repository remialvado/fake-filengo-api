<?php

namespace AppBundle\Controller;

use AppBundle\Engine\FinancialServiceEngine;
use AppBundle\Model\FinancialService;
use AppBundle\Provider\MongoUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @DI\Service("filengo.rules.controller")
 */
class RulesController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function getNextInfo(Request $request)
    {
        $this->checkToken($request);
        $userToken = $request->get("token");
        $user = $this->mongoUserProvider->getByToken($userToken);
        if (!isset($user)) throw new NotFoundHttpException("Can't find user with token '$userToken'");

        $nextInfo = $this->financialServiceEngine->getNextRequiredInfo($user);
        if (!isset($nextInfo)) {
            return new Response('', 204);
        }
        $matchingFinancialServices = [];
        foreach ($this->financialServiceEngine->getPossiblyMatchingFinancialServices($user) as $financialService) {
            $matchingFinancialServices[] = new FinancialService($financialService->getName(), $financialService->getType());
        }

        return new JsonResponse([
            "next-question" => $nextInfo,
            "matching-financial-services" => $matchingFinancialServices
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getAvailableFinancialServices(Request $request)
    {
        $this->checkToken($request);
        $userToken = $request->get("token");
        $user = $this->mongoUserProvider->getByToken($userToken);
        if (!isset($user)) throw new NotFoundHttpException("Can't find user with token '$userToken'");

        $matchingFinancialServices = [];
        foreach ($this->financialServiceEngine->getPossiblyMatchingFinancialServices($user) as $financialService) {
            $matchingFinancialServices[] = new FinancialService($financialService->getName(), $financialService->getType());
        }

        return new JsonResponse($matchingFinancialServices);
    }

    /**
     * @var MongoUserProvider
     * @DI\Inject("filengo.user.provider.mongo")
     */
    public $mongoUserProvider;

    /**
     * @var FinancialServiceEngine
     * @DI\Inject("filengo.financial.service.engine")
     */
    public $financialServiceEngine;
}
