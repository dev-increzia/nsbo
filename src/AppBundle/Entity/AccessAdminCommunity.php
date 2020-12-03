<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessAdminCommunity
 *
 * @ORM\Table(name="access_admin_community")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AccessAdminCommunityRepository")
 */
class AccessAdminCommunity
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
     * @var boolean
     *
     * @ORM\Column(name="isSuAdmin", type="boolean", nullable=true)
     */
    private $isSuAdmin;
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="access", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $accessUsers;
    
    

    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="access")
     * @ORM\JoinColumn(name="community_id", referencedColumnName="id", nullable=false)
     */
    protected $community;
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AdminAccess", inversedBy="adminAccess")
     * @ORM\JoinColumn(name="access_id", referencedColumnName="id", nullable=false)
     */
    protected $access;

    

    /**
     * Set access
     *
     * @param string $access
     *
     * @return AccessAdminCommunity
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get access
     *
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }
    
    public function getIsSuAdmin()
    {
        return $this->isSuAdmin;
    }

    public function setIsSuAdmin($isSuAdmin)
    {
        $this->isSuAdmin = $isSuAdmin;
    }



    /**
     * Set accessUsers
     *
     * @param \UserBundle\Entity\User $accessUsers
     *
     * @return AccessAdminCommunity
     */
    public function setAccessUsers(\UserBundle\Entity\User $accessUsers)
    {
        $this->accessUsers = $accessUsers;

        return $this;
    }

    /**
     * Get accessUsers
     *
     * @return \UserBundle\Entity\User
     */
    public function getAccessUsers()
    {
        return $this->accessUsers;
    }

    /**
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return AccessAdminCommunity
     */
    public function setCommunity(\AppBundle\Entity\Community $community)
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
