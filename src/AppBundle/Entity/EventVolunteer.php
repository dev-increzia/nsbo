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
 * EventVolunteer
 *
 * @ORM\Table(name="event_volunteers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventVolunteerRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class EventVolunteer
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @SerializedName("id")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="eventVolunteer", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Expose
     * @SerializedName("user")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event", inversedBy="volunteers", cascade={"persist"} )
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Expose
     * @SerializedName("event")
     */
    private $event;

    /**
     * @ORM\Column(name="type", type="string", length=255, columnDefinition="enum('mail', 'phone')", nullable=true)
     * @Expose
     * @SerializedName("type")
     */
    private $type;

    /**
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("value")
     */
    private $value;

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
     * Set phone
     *
     * @param string $phone
     *
     * @return EventVolunteer
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return EventVolunteer
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    
        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return EventVolunteer
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return EventVolunteer
     */
    public function setEvent(\AppBundle\Entity\Event $event)
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
     * Set type
     *
     * @param string $type
     *
     * @return EventVolunteer
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
     * Set value
     *
     * @param string $value
     *
     * @return EventVolunteer
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
