<?php
namespace ScummVM\Objects;

/**
 * The BaseObject class is inherited by all other objects and houses all common
 * functions.
 */
abstract class BaseObject
{
    protected $name;
    protected $description;

    public function __construct($data)
    {
        $this->description = $data['description'];
        $this->name = $data['name'];
    }

    public function __toString()
    {
        return $this->getName();
    }

     /* Get the name. */
    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * If the input array doesn't contain the numerical key 0, wrap it inside
     * an array. This functions operates on the data directly.
     *
     * @param mixed $data the input
     */
    public function toArray(&$data)
    {
        if (!is_array($data) || !array_key_exists(0, $data)) {
            $data = array($data);
        }
    }
}
