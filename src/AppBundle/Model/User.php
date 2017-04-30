<?php

namespace AppBundle\Model;

class User implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var array
     */
    protected $infos = [];

    /**
     * User constructor.
     * @param array $contentAsArray
     */
    public function __construct($contentAsArray = [])
    {
        $this->id        = $contentAsArray["_id"] ?? null;
        $this->token     = $contentAsArray["token"] ?? null;
        $this->email     = $contentAsArray["email"] ?? null;
        $this->firstName = $contentAsArray["firstName"] ?? null;
        $this->lastName  = $contentAsArray["lastName"] ?? null;
        $this->infos     = $contentAsArray["infos"] ?? [];
    }

    /**
     * @return User
     */
    public static function getOne()
    {
        return new self();
    }

    function jsonSerialize()
    {
        return [
            "_id"       => $this->id,
            "token"     => $this->token,
            "email"     => $this->email,
            "firstName" => $this->firstName,
            "lastName"  => $this->lastName,
            "infos"     => $this->infos
        ];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return array
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * @param array $infos
     * @return User
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return User
     */
    public function setInfo($key, $value)
    {
        $this->infos[$key] = $value;
        return $this;
    }

    /**
     * @param string$key
     * @param mixed $default
     * @return mixed
     */
    public function getInfo($key, $default = null)
    {
        return $this->infos[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasInfo($key)
    {
        return array_key_exists($key, $this->infos);
    }
}