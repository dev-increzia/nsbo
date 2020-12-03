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
 * Association
 *
 * @ORM\Table(name="association_deleted")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssociationDeletedRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AssociationDeleted
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @SerializedName("id")
     * @Type("integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     * @Expose
     * @SerializedName("createAt")
     * @Type("DateTime")
     */
    private $createAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $createBy;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City", cascade={"persist"}, inversedBy="associations")
     * @Expose
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=false)
     */
    private $updateAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $updateBy;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("name")
     * @Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("address")
     * @Type("string")
     */
    private $address;
    
    /**
     * @var string
     *
     * @ORM\Column(name="codePostal", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("codePostal")
     * @Type("string")
     */
    private $codePostal;
    
    

    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Expose
     * @SerializedName("description")
     * @Type("string")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category", cascade={"persist"}, inversedBy="associations")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("category")
     */
    private $category;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $image;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("email")
     * @Type("string")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("phone")
     * @Type("string")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="moderate", type="string", length=255, columnDefinition="enum('wait', 'accepted', 'refuse')", nullable=false)
     * @Expose
     * @SerializedName("moderate")
     * @Type("string")
     */
    private $moderate = 'wait';

    /**
     * @var string
     *
     * @ORM\Column(name="moderate_at", type="datetime", nullable=true)
     */
    private $moderateAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="monday", type="boolean")
     * @Expose
     * @SerializedName("monday")
     */
    private $monday = false;

    /**
     * @var string
     *
     * @ORM\Column(name="mondayHour", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("mondayHour")
     */
    private $mondayHour;

    /**
     * @var string
     *
     * @ORM\Column(name="mondayHourEnd", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("mondayHourEnd")
     */
    private $mondayHourEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="tuesday", type="boolean")
     * @Expose
     * @SerializedName("tuesday")
     */
    private $tuesday = false;

    /**
     * @var string
     *
     * @ORM\Column(name="tuesdayHour", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("tuesdayHour")
     */
    private $tuesdayHour;

    /**
     * @var string
     *
     * @ORM\Column(name="tuesdayHourEnd", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("tuesdayHourEnd")
     */
    private $tuesdayHourEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="wednesday", type="boolean")
     * @Expose
     * @SerializedName("wednesday")
     */
    private $wednesday = false;

    /**
     * @var string
     *
     * @ORM\Column(name="wednesdayHour", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("wednesdayHour")
     */
    private $wednesdayHour;

    /**
     * @var string
     *
     * @ORM\Column(name="wednesdayHourEnd", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("wednesdayHourEnd")
     */
    private $wednesdayHourEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="thursday", type="boolean")
     * @Expose
     * @SerializedName("thursday")
     */
    private $thursday = false;

    /**
     * @var string
     *
     * @ORM\Column(name="thursdayHour", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("thursdayHour")
     */
    private $thursdayHour;

    /**
     * @var string
     *
     * @ORM\Column(name="thursdayHourEnd", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("thursdayHourEnd")
     */
    private $thursdayHourEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="friday", type="boolean")
     * @Expose
     * @SerializedName("friday")
     */
    private $friday = false;

    /**
     * @var string
     *
     * @ORM\Column(name="fridayHour", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("fridayHour")
     */
    private $fridayHour;

    /**
     * @var string
     *
     * @ORM\Column(name="fridayHourEnd", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("fridayHourEnd")
     */
    private $fridayHourEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="saturday", type="boolean")
     * @Expose
     * @SerializedName("saturday")
     */
    private $saturday = false;

    /**
     * @var string
     *
     * @ORM\Column(name="saturdayHour", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("saturdayHour")
     */
    private $saturdayHour;

    /**
     * @var string
     *
     * @ORM\Column(name="saturdayHourEnd", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("saturdayHourEnd")
     */
    private $saturdayHourEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sunday", type="boolean")
     * @Expose
     * @SerializedName("sunday")
     */
    private $sunday = false;

    /**
     * @var string
     *
     * @ORM\Column(name="sundayHour", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("sundayHour")
     */
    private $sundayHour;

    /**
     * @var string
     *
     * @ORM\Column(name="sundayHourEnd", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("sundayHourEnd")
     */
    private $sundayHourEnd;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="associations")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $community;


    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"}, inversedBy="associationsSuAdmin")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     */
    private $suAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event", mappedBy="association", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="association", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Article", mappedBy="association", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $articles;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="association", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $notifications;

    /**
     * @var string
     * @Expose
     * @SerializedName("role")
     * @Type("string")
     */
    private $role;
    
    /**
     * @var string
     *
     * @ORM\Column(name="timing", type="text", nullable=true)
     * @Expose
     * @SerializedName("timing")
     * @Type("string")
     */
    private $timing;
    
    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text", nullable=true)
     * @Expose
     * @SerializedName("reason")
     * @Type("string")
     */
    private $reason;

  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->setUpdateAt(new \DateTime('now'));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->setCreateAt(new \DateTime('now'));
        $this->setUpdateAt(new \DateTime('now'));
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

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return AssociationDeleted
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
     * Set updateAt
     *
     * @param \DateTime $updateAt
     *
     * @return AssociationDeleted
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return AssociationDeleted
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
     * Set address
     *
     * @param string $address
     *
     * @return AssociationDeleted
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     *
     * @return AssociationDeleted
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return AssociationDeleted
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return AssociationDeleted
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
     * Set email
     *
     * @param string $email
     *
     * @return AssociationDeleted
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return AssociationDeleted
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
     * Set moderate
     *
     * @param string $moderate
     *
     * @return AssociationDeleted
     */
    public function setModerate($moderate)
    {
        $this->moderate = $moderate;

        return $this;
    }

    /**
     * Get moderate
     *
     * @return string
     */
    public function getModerate()
    {
        return $this->moderate;
    }

    /**
     * Set moderateAt
     *
     * @param \DateTime $moderateAt
     *
     * @return AssociationDeleted
     */
    public function setModerateAt($moderateAt)
    {
        $this->moderateAt = $moderateAt;

        return $this;
    }

    /**
     * Get moderateAt
     *
     * @return \DateTime
     */
    public function getModerateAt()
    {
        return $this->moderateAt;
    }

    /**
     * Set monday
     *
     * @param boolean $monday
     *
     * @return AssociationDeleted
     */
    public function setMonday($monday)
    {
        $this->monday = $monday;

        return $this;
    }

    /**
     * Get monday
     *
     * @return boolean
     */
    public function getMonday()
    {
        return $this->monday;
    }

    /**
     * Set mondayHour
     *
     * @param string $mondayHour
     *
     * @return AssociationDeleted
     */
    public function setMondayHour($mondayHour)
    {
        $this->mondayHour = $mondayHour;

        return $this;
    }

    /**
     * Get mondayHour
     *
     * @return string
     */
    public function getMondayHour()
    {
        return $this->mondayHour;
    }

    /**
     * Set mondayHourEnd
     *
     * @param string $mondayHourEnd
     *
     * @return AssociationDeleted
     */
    public function setMondayHourEnd($mondayHourEnd)
    {
        $this->mondayHourEnd = $mondayHourEnd;

        return $this;
    }

    /**
     * Get mondayHourEnd
     *
     * @return string
     */
    public function getMondayHourEnd()
    {
        return $this->mondayHourEnd;
    }

    /**
     * Set tuesday
     *
     * @param boolean $tuesday
     *
     * @return AssociationDeleted
     */
    public function setTuesday($tuesday)
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    /**
     * Get tuesday
     *
     * @return boolean
     */
    public function getTuesday()
    {
        return $this->tuesday;
    }

    /**
     * Set tuesdayHour
     *
     * @param string $tuesdayHour
     *
     * @return AssociationDeleted
     */
    public function setTuesdayHour($tuesdayHour)
    {
        $this->tuesdayHour = $tuesdayHour;

        return $this;
    }

    /**
     * Get tuesdayHour
     *
     * @return string
     */
    public function getTuesdayHour()
    {
        return $this->tuesdayHour;
    }

    /**
     * Set tuesdayHourEnd
     *
     * @param string $tuesdayHourEnd
     *
     * @return AssociationDeleted
     */
    public function setTuesdayHourEnd($tuesdayHourEnd)
    {
        $this->tuesdayHourEnd = $tuesdayHourEnd;

        return $this;
    }

    /**
     * Get tuesdayHourEnd
     *
     * @return string
     */
    public function getTuesdayHourEnd()
    {
        return $this->tuesdayHourEnd;
    }

    /**
     * Set wednesday
     *
     * @param boolean $wednesday
     *
     * @return AssociationDeleted
     */
    public function setWednesday($wednesday)
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    /**
     * Get wednesday
     *
     * @return boolean
     */
    public function getWednesday()
    {
        return $this->wednesday;
    }

    /**
     * Set wednesdayHour
     *
     * @param string $wednesdayHour
     *
     * @return AssociationDeleted
     */
    public function setWednesdayHour($wednesdayHour)
    {
        $this->wednesdayHour = $wednesdayHour;

        return $this;
    }

    /**
     * Get wednesdayHour
     *
     * @return string
     */
    public function getWednesdayHour()
    {
        return $this->wednesdayHour;
    }

    /**
     * Set wednesdayHourEnd
     *
     * @param string $wednesdayHourEnd
     *
     * @return AssociationDeleted
     */
    public function setWednesdayHourEnd($wednesdayHourEnd)
    {
        $this->wednesdayHourEnd = $wednesdayHourEnd;

        return $this;
    }

    /**
     * Get wednesdayHourEnd
     *
     * @return string
     */
    public function getWednesdayHourEnd()
    {
        return $this->wednesdayHourEnd;
    }

    /**
     * Set thursday
     *
     * @param boolean $thursday
     *
     * @return AssociationDeleted
     */
    public function setThursday($thursday)
    {
        $this->thursday = $thursday;

        return $this;
    }

    /**
     * Get thursday
     *
     * @return boolean
     */
    public function getThursday()
    {
        return $this->thursday;
    }

    /**
     * Set thursdayHour
     *
     * @param string $thursdayHour
     *
     * @return AssociationDeleted
     */
    public function setThursdayHour($thursdayHour)
    {
        $this->thursdayHour = $thursdayHour;

        return $this;
    }

    /**
     * Get thursdayHour
     *
     * @return string
     */
    public function getThursdayHour()
    {
        return $this->thursdayHour;
    }

    /**
     * Set thursdayHourEnd
     *
     * @param string $thursdayHourEnd
     *
     * @return AssociationDeleted
     */
    public function setThursdayHourEnd($thursdayHourEnd)
    {
        $this->thursdayHourEnd = $thursdayHourEnd;

        return $this;
    }

    /**
     * Get thursdayHourEnd
     *
     * @return string
     */
    public function getThursdayHourEnd()
    {
        return $this->thursdayHourEnd;
    }

    /**
     * Set friday
     *
     * @param boolean $friday
     *
     * @return AssociationDeleted
     */
    public function setFriday($friday)
    {
        $this->friday = $friday;

        return $this;
    }

    /**
     * Get friday
     *
     * @return boolean
     */
    public function getFriday()
    {
        return $this->friday;
    }

    /**
     * Set fridayHour
     *
     * @param string $fridayHour
     *
     * @return AssociationDeleted
     */
    public function setFridayHour($fridayHour)
    {
        $this->fridayHour = $fridayHour;

        return $this;
    }

    /**
     * Get fridayHour
     *
     * @return string
     */
    public function getFridayHour()
    {
        return $this->fridayHour;
    }

    /**
     * Set fridayHourEnd
     *
     * @param string $fridayHourEnd
     *
     * @return AssociationDeleted
     */
    public function setFridayHourEnd($fridayHourEnd)
    {
        $this->fridayHourEnd = $fridayHourEnd;

        return $this;
    }

    /**
     * Get fridayHourEnd
     *
     * @return string
     */
    public function getFridayHourEnd()
    {
        return $this->fridayHourEnd;
    }

    /**
     * Set saturday
     *
     * @param boolean $saturday
     *
     * @return AssociationDeleted
     */
    public function setSaturday($saturday)
    {
        $this->saturday = $saturday;

        return $this;
    }

    /**
     * Get saturday
     *
     * @return boolean
     */
    public function getSaturday()
    {
        return $this->saturday;
    }

    /**
     * Set saturdayHour
     *
     * @param string $saturdayHour
     *
     * @return AssociationDeleted
     */
    public function setSaturdayHour($saturdayHour)
    {
        $this->saturdayHour = $saturdayHour;

        return $this;
    }

    /**
     * Get saturdayHour
     *
     * @return string
     */
    public function getSaturdayHour()
    {
        return $this->saturdayHour;
    }

    /**
     * Set saturdayHourEnd
     *
     * @param string $saturdayHourEnd
     *
     * @return AssociationDeleted
     */
    public function setSaturdayHourEnd($saturdayHourEnd)
    {
        $this->saturdayHourEnd = $saturdayHourEnd;

        return $this;
    }

    /**
     * Get saturdayHourEnd
     *
     * @return string
     */
    public function getSaturdayHourEnd()
    {
        return $this->saturdayHourEnd;
    }

    /**
     * Set sunday
     *
     * @param boolean $sunday
     *
     * @return AssociationDeleted
     */
    public function setSunday($sunday)
    {
        $this->sunday = $sunday;

        return $this;
    }

    /**
     * Get sunday
     *
     * @return boolean
     */
    public function getSunday()
    {
        return $this->sunday;
    }

    /**
     * Set sundayHour
     *
     * @param string $sundayHour
     *
     * @return AssociationDeleted
     */
    public function setSundayHour($sundayHour)
    {
        $this->sundayHour = $sundayHour;

        return $this;
    }

    /**
     * Get sundayHour
     *
     * @return string
     */
    public function getSundayHour()
    {
        return $this->sundayHour;
    }

    /**
     * Set sundayHourEnd
     *
     * @param string $sundayHourEnd
     *
     * @return AssociationDeleted
     */
    public function setSundayHourEnd($sundayHourEnd)
    {
        $this->sundayHourEnd = $sundayHourEnd;

        return $this;
    }

    /**
     * Get sundayHourEnd
     *
     * @return string
     */
    public function getSundayHourEnd()
    {
        return $this->sundayHourEnd;
    }

    /**
     * Set timing
     *
     * @param string $timing
     *
     * @return AssociationDeleted
     */
    public function setTiming($timing)
    {
        $this->timing = $timing;

        return $this;
    }

    /**
     * Get timing
     *
     * @return string
     */
    public function getTiming()
    {
        return $this->timing;
    }

    /**
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return AssociationDeleted
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
     * Set updateBy
     *
     * @param \UserBundle\Entity\User $updateBy
     *
     * @return AssociationDeleted
     */
    public function setUpdateBy(\UserBundle\Entity\User $updateBy = null)
    {
        $this->updateBy = $updateBy;

        return $this;
    }

    /**
     * Get updateBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getUpdateBy()
    {
        return $this->updateBy;
    }

    

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return AssociationDeleted
     */
    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return AssociationDeleted
     */
    public function setImage(\AppBundle\Entity\File $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \AppBundle\Entity\File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return Article
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
     * Set suAdmin
     *
     * @param \UserBundle\Entity\User $suAdmin
     *
     * @return AssociationDeleted
     */
    public function setSuAdmin(\UserBundle\Entity\User $suAdmin = null)
    {
        $this->suAdmin = $suAdmin;

        return $this;
    }

    /**
     * Get suAdmin
     *
     * @return \UserBundle\Entity\User
     */
    public function getSuAdmin()
    {
        return $this->suAdmin;
    }

    /**
     * Add event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return AssociationDeleted
     */
    public function addEvent(\AppBundle\Entity\Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param \AppBundle\Entity\Event $event
     */
    public function removeEvent(\AppBundle\Entity\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return AssociationDeleted
     */
    public function addComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comment $comment
     */
    public function removeComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return AssociationDeleted
     */
    public function addArticle(\AppBundle\Entity\Article $article)
    {
        $this->articles[] = $article;

        return $this;
    }

    /**
     * Remove article
     *
     * @param \AppBundle\Entity\Article $article
     */
    public function removeArticle(\AppBundle\Entity\Article $article)
    {
        $this->articles->removeElement($article);
    }

    /**
     * Get articles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return AssociationDeleted
     */
    public function addNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \AppBundle\Entity\Notification $notification
     */
    public function removeNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set reason
     *
     * @param string $reason
     *
     * @return AssociationDeleted
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return Association
     */
    public function setCity(\AppBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }
}
