<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * CommunityUsers
 *
 * @ORM\Table(name="community_users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommunityUsersRepository")
 */
class CommunityUsers
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
     * @var bool
     *
     * @ORM\Column(name="follow", type="boolean")
     */
    private $follow;

    /**
     *
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="communities")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="users")
     * @ORM\JoinColumn(name="community_id", referencedColumnName="id", nullable=false)
     */
    protected $community;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, columnDefinition="enum('pending', 'refused','approved')", nullable=false)
     * @Expose
     * @SerializedName("status")
     */
    private $type = 'approved';

    

    /**
     * Set follow
     *
     * @param boolean $follow
     *
     * @return CommunityUsers
     */
    public function setFollow($follow)
    {
        $this->follow = $follow;

        return $this;
    }

    /**
     * Get follow
     *
     * @return bool
     */
    public function getFollow()
    {
        return $this->follow;
    }
    
    
    public function getUser()
    {
        return $this->user;
    }

    public function getCommunity()
    {
        return $this->community;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setCommunity($community)
    {
        $this->community = $community;
    }



    /**
     * Set type
     *
     * @param string $type
     *
     * @return CommunityUsers
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
