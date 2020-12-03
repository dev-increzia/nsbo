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
 * AssociationUser
 *
 * @ORM\Table(name="user_association")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssociationUserRepository")
 */
class AssociationUser
{


    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="associations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", inversedBy="users")
     * @ORM\JoinColumn(name="association_id", referencedColumnName="id", nullable=false)
     */
    protected $association;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, columnDefinition="enum('pending', 'refused','approved')", nullable=false)
     * @Expose
     * @SerializedName("status")
     */
    private $type = 'approved';


    /**
     * Set type
     *
     * @param string $type
     *
     * @return AssociationUser
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
     * @return AssociationUser
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
     * Set association
     *
     * @param \AppBundle\Entity\Association $association
     *
     * @return AssociationUser
     */
    public function setAssociation(\AppBundle\Entity\Association $association)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return \AppBundle\Entity\Association
     */
    public function getAssociation()
    {
        return $this->association;
    }
}
