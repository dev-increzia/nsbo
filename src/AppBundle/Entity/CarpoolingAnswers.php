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
 * CarpoolingAnswers
 *
 * @ORM\Table(name="carpooling_answers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarpoolingAnswersRepository")
 */
class CarpoolingAnswers
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
     * @ORM\Column(name="phoneNumber", type="string", length=255)
     */
    private $phoneNumber;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Carpooling", cascade={"persist"}, inversedBy="carpoolingAnswers")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $carpooling;
    /**
     * @var string
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     *
     */
    private $createAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("create_by")
     */
    private $createBy;
    
    


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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return CarpoolingAnswers
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set carpooling
     *
     * @param \AppBundle\Entity\Carpooling $carpooling
     *
     * @return CarpoolingAnswers
     */
    public function setCarpooling(\AppBundle\Entity\Carpooling $carpooling = null)
    {
        $this->carpooling = $carpooling;

        return $this;
    }

    /**
     * Get carpooling
     *
     * @return \AppBundle\Entity\Carpooling
     */
    public function getCarpooling()
    {
        return $this->carpooling;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return CarpoolingAnswers
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get createAt
     *
     * @return \DateTime
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return CarpoolingAnswers
     */
    public function setCreateBy(\UserBundle\Entity\User $createBy = null)
    {
        $this->createBy = $createBy;

        return $this;
    }

    /**
     * Get createBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getCreateBy()
    {
        return $this->createBy;
    }
}
