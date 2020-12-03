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
 * Carpooling
 *
 * @ORM\Table(name="carpooling")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarpoolingRepository")
 */
class Carpooling
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
     * @var string
     *
     * @ORM\Column(name="relayPoint", type="string", length=255)
     */
    private $relayPoint;

    /**
     * @var string
     *
     * @ORM\Column(name="meetingTime", type="time", length=255)
     */
    private $meetingTime;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event", cascade={"persist"}, inversedBy="carpoolings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $event;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CarpoolingAnswers", mappedBy="carpooling", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $carpoolingAnswers;

    /**
     * @var int
     *
     * @ORM\Column(name="nbPlaces", type="integer")
     */
    private $nbPlaces;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string", length=255)
     */
    private $phoneNumber;


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
     * Set relayPoint
     *
     * @param string $relayPoint
     *
     * @return Carpooling
     */
    public function setRelayPoint($relayPoint)
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }

    /**
     * Get relayPoint
     *
     * @return string
     */
    public function getRelayPoint()
    {
        return $this->relayPoint;
    }

    /**
     * Set meetingTime
     *
     * @param string $meetingTime
     *
     * @return Carpooling
     */
    public function setMeetingTime($meetingTime)
    {
        $this->meetingTime = $meetingTime;

        return $this;
    }

    /**
     * Get meetingTime
     *
     * @return string
     */
    public function getMeetingTime()
    {
        return $this->meetingTime;
    }

    /**
     * Set nbPlaces
     *
     * @param integer $nbPlaces
     *
     * @return Carpooling
     */
    public function setNbPlaces($nbPlaces)
    {
        $this->nbPlaces = $nbPlaces;

        return $this;
    }

    /**
     * Get nbPlaces
     *
     * @return int
     */
    public function getNbPlaces()
    {
        return $this->nbPlaces;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return Carpooling
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Carpooling
     */
    public function setEvent(\AppBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->carpoolingAnswers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return Carpooling
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
     * @return Carpooling
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

    /**
     * Add carpoolingAnswer
     *
     * @param \AppBundle\Entity\CarpoolingAnswers $carpoolingAnswer
     *
     * @return Carpooling
     */
    public function addCarpoolingAnswer(\AppBundle\Entity\CarpoolingAnswers $carpoolingAnswer)
    {
        $this->carpoolingAnswers[] = $carpoolingAnswer;

        return $this;
    }

    /**
     * Remove carpoolingAnswer
     *
     * @param \AppBundle\Entity\CarpoolingAnswers $carpoolingAnswer
     */
    public function removeCarpoolingAnswer(\AppBundle\Entity\CarpoolingAnswers $carpoolingAnswer)
    {
        $this->carpoolingAnswers->removeElement($carpoolingAnswer);
    }

    /**
     * Get carpoolingAnswers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarpoolingAnswers()
    {
        return $this->carpoolingAnswers;
    }
}
