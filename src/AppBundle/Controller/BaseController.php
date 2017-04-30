<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BaseController
{
    protected static $applicationTokens = ["abcd" => "wizbii"];

    protected function checkToken(Request $request)
    {
        $token = $this->getToken($request);
        if (!array_key_exists($token, self::$applicationTokens)) {
            throw new AccessDeniedHttpException("bad token : '$token'");
        }
    }

    protected function getToken(Request $request)
    {
        $bearerLength = strlen("Bearer ");
        $authorizationHeader = $request->headers->get("Authorization", "");
        if (strlen($authorizationHeader) < $bearerLength) return "";
        return substr($authorizationHeader, $bearerLength);
    }
}