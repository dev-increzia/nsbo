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
 * Interest
 *
 * @ORM\Table(name="interest")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InterestRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Interest
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
     */
    private $createAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $createBy;

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
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("title")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Expose
     * @SerializedName("description")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("address")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", nullable=true)
     * @Expose
     * @SerializedName("longitude")
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", nullable=true)
     * @Expose
     * @SerializedName("latitude")
     */
    private $latitude;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\InterestCategory", cascade={"persist"}, inversedBy="interests")
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
     * @var string
     * @Expose
     * @SerializedName("image_url")
     * @Type("string")
     */
    private $imageURL;

    public function getImageURL()
    {
        return $this->imageURL;
    }

    public function setImageURL($imageURL)
    {
        $this->imageURL = $imageURL;
        return $this;
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
     * @return Interest
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
     * @return Interest
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
     * Set title
     *
     * @param string $title
     *
     * @return Interest
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Interest
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
     * Set address
     *
     * @param string $address
     *
     * @return Interest
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
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Interest
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Interest
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Interest
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
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return Interest
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
     * @return Interest
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
     * @param \AppBundle\Entity\InterestCategory $category
     *
     * @return Interest
     */
    public function setCategory(\AppBundle\Entity\InterestCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\InterestCategory
     */
    public function getCategory()
    {
        return $this->category;
    }



    /**
     * Set email
     *
     * @param string $email
     *
     * @return Interest
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
     * @return Interest
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
     * Set image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Interest
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
     * Set monday
     *
     * @param boolean $monday
     *
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
     * @return Interest
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
}
