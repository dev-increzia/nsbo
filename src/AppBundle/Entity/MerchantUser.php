<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * MerchantUser
 *
 * @ORM\Table(name="user_merchant")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MerchantUserRepository")
 */
class MerchantUser
{
    /**
     * @var bool
     *
     * @ORM\Column(name="follow", type="boolean", nullable=true)
     */
    private $follow;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="merchant")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Merchant", inversedBy="users")
     * @ORM\JoinColumn(name="merchant_id", referencedColumnName="id", nullable=false)
     */
    protected $merchant;
    
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return MerchantUser
     */
    public function setUser(\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set merchant
     *
     * @param \AppBundle\Entity\Merchant $merchant
     *
     * @return MerchantUser
     */
    public function setMerchant(\AppBundle\Entity\Merchant $merchant)
    {
        $this->merchant = $merchant;

        return $this;
    }

    /**
     * Get merchant
     *
     * @return \AppBundle\Entity\Merchant
     */
    public function getMerchant()
    {
        return $this->merchant;
    }
}
