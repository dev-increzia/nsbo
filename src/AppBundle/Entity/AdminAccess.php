<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminAccess
 *
 * @ORM\Table(name="admin_access")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdminAccessRepository")
 */
class AdminAccess
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AccessAdminCommunity", mappedBy="access", cascade={"persist"})
     */
    private $adminAccess;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_private", type="boolean")
     */
    private $isPrivate = false;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return AdminAccess
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return AdminAccess
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    
    
    public function __toString()
    {
        return $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->adminAccess = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add adminAccess
     *
     * @param \AppBundle\Entity\AccessAdminCommunity $adminAccess
     *
     * @return AdminAccess
     */
    public function addAdminAccess(\AppBundle\Entity\AccessAdminCommunity $adminAccess)
    {
        $this->adminAccess[] = $adminAccess;

        return $this;
    }

    /**
     * Remove adminAccess
     *
     * @param \AppBundle\Entity\AccessAdminCommunity $adminAccess
     */
    public function removeAdminAccess(\AppBundle\Entity\AccessAdminCommunity $adminAccess)
    {
        $this->adminAccess->removeElement($adminAccess);
    }

    /**
     * Get adminAccess
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdminAccess()
    {
        return $this->adminAccess;
    }

    /**
     * Set isPrivate
     *
     * @param boolean $isPrivate
     *
     * @return AdminAccess
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    /**
     * Get isPrivate
     *
     * @return boolean
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }
}
