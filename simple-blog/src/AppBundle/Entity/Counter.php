<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="counter")
 */
class Counter {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;


    /**
     * @ORM\Column(type="string")
     */
    public $name;

    /**
     * @ORM\Column(type="integer")
     */
    public $value;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param integer $name
     *
     * @return Counter
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return integer
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param integer $value
     *
     * @return Counter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }
}
