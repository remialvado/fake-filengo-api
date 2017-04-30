<?php

namespace AppBundle\Model;

class Info implements \JsonSerializable
{
    const TYPE_INTEGER = "integer";
    const TYPE_FLOAT   = "float";
    const TYPE_BOOLEAN = "boolean";
    const TYPE_DATE    = "date";
    const TYPE_CHOICE  = "choice";
    const TYPE_GEOLOC  = "geoloc";

    const GROUP_PERSONAL_INFOS = "personal_infos";
    const GROUP_EDUCATION      = "education";
    const GROUP_HOUSING        = "housing";
    const GROUP_RESOURCES      = "resources";

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string[]
     */
    protected $keys = [];

    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var integer
     */
    protected $priority;

    /**
     * Info constructor.
     * @param string $id
     * @param string[] $keys
     * @param string $group
     * @param string $type
     * @param array $options
     * @param int $priority
     */
    public function __construct($id = null, $keys = [], $group = null, $type = null, array $options = [], $priority = 0)
    {
        $this->id = $id;
        $this->keys = $keys;
        $this->group = $group;
        $this->type = $type;
        $this->options = $options;
        $this->priority = $priority;
    }

    function jsonSerialize()
    {
        return [
            "id"       => $this->id,
            "keys"     => $this->keys,
            "group"    => $this->group,
            "type"     => $this->type,
            "options"  => $this->options,
            "priority" => $this->priority
        ];
    }

    /**
     * @param string $id
     * @return Info
     */
    public static function getOne($id)
    {
        return new Info($id, ["value" => $id]);
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
     * @return Info
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Syntactic sugar
     * @param string $key
     * @return Info
     */
    public function setKey($key)
    {
        $this->setKeys([$key]);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @param string[] $keys
     * @return Info
     */
    public function setKeys($keys)
    {
        $this->keys = $keys;
        return $this;
    }

    /**
     * @param string $key
     * @param string $role
     * @return Info
     */
    public function addKey($key, $role = "value")
    {
        $this->keys[$role] = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return Info
     */
    public function setGroup($group)
    {
        $this->group = $group;
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
     * @return Info
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return Info
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Info
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return Info
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }
}