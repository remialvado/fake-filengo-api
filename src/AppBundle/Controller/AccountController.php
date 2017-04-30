<?php

namespace AppBundle\Controller;

use AppBundle\Engine\FinancialServiceEngine;
use AppBundle\Model\FinancialService;
use AppBundle\Model\User;
use AppBundle\Provider\MongoUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @DI\Service("filengo.account.controller")
 */
class AccountController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function createAccount(Request $request)
    {
        $this->checkToken($request);
        $content = json_decode($request->getContent(), true);
        $user = User::getOne()
            ->setEmail($content["email"])
            ->setFirstName($content["firstName"])
            ->setLastName($content["lastName"]);
        $user->setId(sha1($user->getFirstName() . $user->getLastName()));
        $this->mongoUserProvider->createUser($user);
        return new JsonResponse(["status" => "ok", "token" => $user->getToken()]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function updateAccount(Request $request)
    {
        $this->checkToken($request);
        $userToken = $request->get("token");
        $user = $this->mongoUserProvider->getByToken($userToken);
        if (!isset($user)) throw new NotFoundHttpException("Can't find user with token '$userToken'");

        $content = json_decode($request->getContent(), true);
        foreach ($content as $key => $value) {
            $user->setInfo($key, $value);
        }
        $this->mongoUserProvider->updateUser($user);

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
