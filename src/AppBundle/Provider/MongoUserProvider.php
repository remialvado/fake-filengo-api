<?php

namespace AppBundle\Provider;

use AppBundle\Model\User;
use JMS\DiExtraBundle\Annotation as DI;
use MongoDB\Client;

/**
 * @DI\Service("filengo.user.provider.mongo")
 */
class MongoUserProvider
{
    /**
     * @param string $id
     * @return User
     */
    public function getById($id)
    {
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->filengo->user;
        $data = $collection->findOne(["_id" => $id]);
        return isset($data) ? new User($data) : null;
    }

    /**
     * @param string $token
     * @return User
     */
    public function getByToken($token)
    {
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->filengo->user;
        $data = $collection->findOne(["token" => $token]);
        return isset($data) ? new User($data) : null;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function createUser($user)
    {
        $user->setToken(sha1($user->getId()));
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->filengo->user;
        $collection->insertOne(json_decode(json_encode($user), true));
        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function updateUser($user)
    {
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->filengo->user;
        $collection->replaceOne(["_id" => $user->getId()], json_decode(json_encode($user), true));
        return true;
    }
}