<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PhoneBookHeading
 *
 * @ORM\Table(name="phone_book_heading")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PhoneBookHeadingRepository")
 */
class PhoneBookHeading extends AbstractHeading
{

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="phonebookHeadings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $community;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CategoryPhoneBookHeading", mappedBy="phoneBookHeading", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $objects;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;


    /**
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return PhoneBookHeading
     */
    public function setCommunity(\AppBundle\Entity\Community $community = null)
    {
        $this->community = $community;

        return $this;
    }

    /**
     * Get community
     *
     * @return \AppBundle\Entity\Community
     */
    public function getCommunity()
    {
        return $this->community;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categoryphoneBookHeading = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add categoryphoneBookHeading
     *
     * @param \AppBundle\Entity\CategoryPhoneBookHeading $categoryphoneBookHeading
     *
     * @return PhoneBookHeading
     */
    public function addCategoryphoneBookHeading(\AppBundle\Entity\CategoryPhoneBookHeading $categoryphoneBookHeading)
    {
        $this->categoryphoneBookHeading[] = $categoryphoneBookHeading;

        return $this;
    }

    /**
     * Remove categoryphoneBookHeading
     *
     * @param \AppBundle\Entity\CategoryPhoneBookHeading $categoryphoneBookHeading
     */
    public function removeCategoryphoneBookHeading(\AppBundle\Entity\CategoryPhoneBookHeading $categoryphoneBookHeading)
    {
        $this->categoryphoneBookHeading->removeElement($categoryphoneBookHeading);
    }

    /**
     * Get categoryphoneBookHeading
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategoryphoneBookHeading()
    {
        return $this->categoryphoneBookHeading;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return PhoneBookHeading
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Add object
     *
     * @param \AppBundle\Entity\CategoryPhoneBookHeading $object
     *
     * @return PhoneBookHeading
     */
    public function addObject(\AppBundle\Entity\CategoryPhoneBookHeading $object)
    {
        $this->objects[] = $object;

        return $this;
    }

    /**
     * Remove object
     *
     * @param \AppBundle\Entity\CategoryPhoneBookHeading $object
     */
    public function removeObject(\AppBundle\Entity\CategoryPhoneBookHeading $object)
    {
        $this->objects->removeElement($object);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    public function havePredefinedObjects()
    {
        if(!empty($this->getObjects()) > 0){
            foreach ($this->getObjects() as $object) {
                if ($object->getName() == 'Commerces/Partenaires' || $object->getName() == 'Groupes/Associations')
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}
