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
 * Event
 *
 * @ORM\Table(name="event", options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Event
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
     * @Expose
     * @SerializedName("title")
     * @ORM\Column(name="title", type="string", length=255, nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City", cascade={"persist"}, inversedBy="events")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="public_at", type="datetime", nullable=true)
     * @Expose
     * @SerializedName("publicAt")
     */
    private $publicAt;

    /**
     * @var string
     * @Expose
     * @SerializedName("place")
     * @ORM\Column(name="place", type="string", length=255, nullable=true)
     */
    private $place;

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
     * @ORM\Column(name="start_at", type="datetime", nullable=false)
     * @Expose
     * @SerializedName("startAt")
     */
    private $startAt;

    /**
     * @var string
     *
     * @ORM\Column(name="end_at", type="datetime", nullable=false)
     * @Expose
     * @SerializedName("endAt")
     */
    private $endAt;

    /**
     * @var string
     * @Expose
     * @SerializedName("description")
     * @ORM\Column(name="description", type="text", nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="event_images",
     *      joinColumns={@ORM\JoinColumn(onDelete="CASCADE",name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE",name="image_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $images;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $image;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $video;
    
    /**
     * @var string
     * @Expose
     * @SerializedName("video_url")
     * @Type("string")
     */
    private $videoURL;
    
    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("price")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="participants_nbre", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("participantsNbre")
     */
    private $participantsNbre;

    /**
     * @var string
     *
     * @ORM\Column(name="reservationUrl", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("reservationurl")
     */
    private $reservationUrl;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", cascade={"persist"}, inversedBy="events")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("association")
     */
    private $association;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="events")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $community;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="events")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $secondaryCommunity;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Category", inversedBy="secondaryCategories", cascade={"persist"})
     * @ORM\JoinTable(name="event_secondary_categories")
     */
    private $secondaryCategories;


    /**
     * @var boolean
     *
     * @ORM\Column(name="toCity", type="boolean")
     */
    private $toCity = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="moderate_secondary_community", type="string", length=255, columnDefinition="enum('wait', 'accepted', 'refuse')", nullable=false)
     */
    private $moderateSecondaryCommunity = 'wait';
    
    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Article", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("article")
     */
    private $article;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Carpooling", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $carpoolings;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pushEnabled", type="boolean")
     * @Expose
     * @SerializedName("pushEnabled")
     */
    private $pushEnabled = false; 

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Push", cascade={"persist"}, mappedBy="event")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     * @Expose
     * @SerializedName("push")
     */
    private $push;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean")
     * @Expose
     * @SerializedName("is_active")
     */
    private $enabled;

    /**
     * @var boolean
     * @ORM\Column(name="is_private", type="boolean")
     * @Expose
     * @SerializedName("is_private")
     */
    private $private = false;

    /**
     * @var boolean
     * @ORM\Column(name="state", type="boolean")
     */
    private $state = false;

    /**
     * @var string
     * @Expose
     * @ORM\Column(name="type", type="string", length=255, columnDefinition="enum('community', 'association','merchant')", nullable=false)
     */
    private $type = 'community';

    /**
     * @var string
     *
     * @ORM\Column(name="moderate", type="string", length=255, columnDefinition="enum('wait', 'accepted', 'refuse')", nullable=false)
     * @Expose
     * @SerializedName("moderate")
     * @Type("string")
     */
    private $moderate;

    /**
     * @var string
     *
     * @ORM\Column(name="moderate_at", type="datetime", nullable=true)
     */
    private $moderateAt;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\EventVolunteer", mappedBy="event", cascade={"persist"})
     * @ORM\JoinTable(name="event_volunteers")
     * @Expose
     * @SerializedName("volunteers")
     */
    private $volunteers;

    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User", inversedBy="eventsParticipant", cascade={"persist"})
     * @ORM\JoinTable(name="event_participants")
     * @Expose
     * @SerializedName("participants")
     */
    private $participants;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Category", inversedBy="events", cascade={"persist"})
     * @ORM\JoinTable(name="event_categories")
     * @Expose
     * @SerializedName("categories")
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\EventReservation", inversedBy="events", cascade={"persist"})
     * @ORM\JoinTable(name="event_reservations")
     * @Expose
     * @SerializedName("reservations")
     *
     */
    private $reservations;

    /**
     * @var boolean
     *
     * @ORM\Column(name="personalized", type="boolean")
     * @Expose
     * @SerializedName("personalized")
     */
    private $personalized = false;

    /**
     * @var string
     *
     * @ORM\Column(name="ageFrom", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("agefrom")
     */
    private $ageFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="ageTo", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("ageto")
     */
    private $ageTo;

    /**
     * @var string
     *
     * @ORM\Column(name="lessThanSix", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("lessthansix")
     */
    private $lessThanSix;

    /**
     * @var string
     *
     * @ORM\Column(name="betweenSixTwelve", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("betweensixtwelve")
     */
    private $betweenSixTwelve;

    /**
     * @var string
     *
     * @ORM\Column(name="betweenTwelveEighteen", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("twelveeighteen")
     */
    private $betweenTwelveEighteen;

    /**
     * @var string
     *
     * @ORM\Column(name="allChildrens", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("allchildrens")
     */
    private $allChildrens;

    /**
     * @var string
     *
     * @ORM\Column(name="monday", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("monday")
     */
    private $monday;

    /**
     * @var string
     *
     * @ORM\Column(name="tuesday", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("tuesday")
     */
    private $tuesday;

    /**
     * @var string
     *
     * @ORM\Column(name="wednesday", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("wednesday")
     */
    private $wednesday;

    /**
     * @var string
     *
     * @ORM\Column(name="thursday", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("thursday")
     */
    private $thursday;

    /**
     * @var string
     *
     * @ORM\Column(name="friday", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("friday")
     */
    private $friday;

    /**
     * @var string
     *
     * @ORM\Column(name="saturday", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("saturday")
     */
    private $saturday;

    /**
     * @var string
     *
     * @ORM\Column(name="sunday", type="boolean", nullable=true)
     * @Expose
     * @SerializedName("sunday")
     */
    private $sunday;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $notifications;

    /**
     * @var string
     * @Expose
     * @SerializedName("nb_volunteers")
     * @Type("string")
     */
    private $nbvolunteers;

    /**
     * @var string
     * @Expose
     * @SerializedName("categoryNames")
     */
    private $categoryNames;

    /**
     * @ORM\ManyToOne(targetEntity="Event", cascade={"persist"}, inversedBy="duplicatedEvents")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Expose
     * @SerializedName("parent")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     */
    private $duplicatedEvents;

    public function getCategoryNames()
    {
        return $this->categoryNames;
    }

    public function setCategoryNames($categoryNames)
    {
        $this->categoryNames = $categoryNames;
        return $this;
    }

    /**
     * Set nbvolunteers
     *
     * @return Event
     */
    public function setNbvolunteers($nbvolunteers)
    {
        $this->nbvolunteers = $nbvolunteers;

        return $this;
    }

    /**
     * Get nbvolunteers
     *
     * @return string
     */
    public function getNbvolunteers()
    {
        return $this->nbvolunteers;
    }

    /**
     * @var string
     * @Expose
     * @SerializedName("nb_participants")
     * @Type("string")
     */
    private $nbparticipants;

    /**
     * Set nbparticipants
     *
     *
     * @return Event
     */
    public function setNbparticipants($nbparticipants)
    {
        $this->nbparticipants = $nbparticipants;

        return $this;
    }

    /**
     * Get nbparticipants
     *
     * @return string
     */
    public function getNbparticipants()
    {
        return $this->nbparticipants;
    }

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
    
    public function getVideoURL()
    {
        return $this->videoURL;
    }

    public function setVideoURL($videoURL)
    {
        $this->videoURL = $videoURL;
        return $this;
    }


    /**
     * @var string
     * @Expose
     * @SerializedName("eventImages")
     */
    private $eventImages;

    public function getEventImages()
    {
        return $this->eventImages;
    }

    public function setEventImages($eventImages)
    {
        $this->eventImages = $eventImages;
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
     * Constructor
     */
    public function __construct()
    {
        $this->volunteers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->participants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * Set place
     *
     * @param string $place
     *
     * @return Event
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     *
     * @return Event
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
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return Event
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set endAt
     *
     * @param \DateTime $endAt
     *
     * @return Event
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Event
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
     * Set price
     *
     * @param string $price
     *
     * @return Event
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set reservationUrl
     *
     * @param string $reservationUrl
     *
     * @return Event
     */
    public function setReservationUrl($reservationUrl)
    {
        $this->reservationUrl = $reservationUrl;

        return $this;
    }

    /**
     * Get reservationUrl
     *
     * @return string
     */
    public function getReservationUrl()
    {
        return $this->reservationUrl;
    }

    /**
     * Set toCity
     *
     * @param boolean $toCity
     *
     * @return Event
     */
    public function setToCity($toCity)
    {
        $this->toCity = $toCity;

        return $this;
    }

    /**
     * Get toCity
     *
     * @return boolean
     */
    public function getToCity()
    {
        return $this->toCity;
    }

    /**
     * Set pushEnabled
     *
     * @param boolean $pushEnabled
     *
     * @return Event
     */
    public function setPushEnabled($pushEnabled)
    {
        $this->pushEnabled = $pushEnabled;

        return $this;
    }

    /**
     * Get pushEnabled
     *
     * @return boolean
     */
    public function getPushEnabled()
    {
        return $this->pushEnabled;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Event
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
     * Set state
     *
     * @param boolean $state
     *
     * @return Event
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return boolean
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Event
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
     * Set moderate
     *
     * @param string $moderate
     *
     * @return Event
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
     * @return Event
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
     * Set personalized
     *
     * @param boolean $personalized
     *
     * @return Event
     */
    public function setPersonalized($personalized)
    {
        $this->personalized = $personalized;

        return $this;
    }

    /**
     * Get personalized
     *
     * @return boolean
     */
    public function getPersonalized()
    {
        return $this->personalized;
    }

    /**
     * Set ageFrom
     *
     * @param string $ageFrom
     *
     * @return Event
     */
    public function setAgeFrom($ageFrom)
    {
        $this->ageFrom = $ageFrom;

        return $this;
    }

    /**
     * Get ageFrom
     *
     * @return string
     */
    public function getAgeFrom()
    {
        return $this->ageFrom;
    }

    /**
     * Set ageTo
     *
     * @param string $ageTo
     *
     * @return Event
     */
    public function setAgeTo($ageTo)
    {
        $this->ageTo = $ageTo;

        return $this;
    }

    /**
     * Get ageTo
     *
     * @return string
     */
    public function getAgeTo()
    {
        return $this->ageTo;
    }

    /**
     * Set lessThanSix
     *
     * @param boolean $lessThanSix
     *
     * @return Event
     */
    public function setLessThanSix($lessThanSix)
    {
        $this->lessThanSix = $lessThanSix;

        return $this;
    }

    /**
     * Get lessThanSix
     *
     * @return boolean
     */
    public function getLessThanSix()
    {
        return $this->lessThanSix;
    }

    /**
     * Set betweenSixTwelve
     *
     * @param boolean $betweenSixTwelve
     *
     * @return Event
     */
    public function setBetweenSixTwelve($betweenSixTwelve)
    {
        $this->betweenSixTwelve = $betweenSixTwelve;

        return $this;
    }

    /**
     * Get betweenSixTwelve
     *
     * @return boolean
     */
    public function getBetweenSixTwelve()
    {
        return $this->betweenSixTwelve;
    }

    /**
     * Set betweenTwelveEighteen
     *
     * @param boolean $betweenTwelveEighteen
     *
     * @return Event
     */
    public function setBetweenTwelveEighteen($betweenTwelveEighteen)
    {
        $this->betweenTwelveEighteen = $betweenTwelveEighteen;

        return $this;
    }

    /**
     * Get betweenTwelveEighteen
     *
     * @return boolean
     */
    public function getBetweenTwelveEighteen()
    {
        return $this->betweenTwelveEighteen;
    }

    /**
     * Set allChildrens
     *
     * @param boolean $allChildrens
     *
     * @return Event
     */
    public function setAllChildrens($allChildrens)
    {
        $this->allChildrens = $allChildrens;

        return $this;
    }

    /**
     * Get allChildrens
     *
     * @return boolean
     */
    public function getAllChildrens()
    {
        return $this->allChildrens;
    }

    /**
     * Set monday
     *
     * @param boolean $monday
     *
     * @return Event
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
     * Set tuesday
     *
     * @param boolean $tuesday
     *
     * @return Event
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
     * Set wednesday
     *
     * @param boolean $wednesday
     *
     * @return Event
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
     * Set thursday
     *
     * @param boolean $thursday
     *
     * @return Event
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
     * Set friday
     *
     * @param boolean $friday
     *
     * @return Event
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
     * Set saturday
     *
     * @param boolean $saturday
     *
     * @return Event
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
     * Set sunday
     *
     * @param boolean $sunday
     *
     * @return Event
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
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return Event
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
     * @return Event
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
     * Set image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Event
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
     * Set video
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Event
     */
    public function setVideo(\AppBundle\Entity\File $video = null)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return \AppBundle\Entity\File
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set association
     *
     * @param \AppBundle\Entity\Association $association
     *
     * @return Event
     */
    public function setAssociation(\AppBundle\Entity\Association $association = null)
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
     * Set article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return Event
     */
    public function setArticle(\AppBundle\Entity\Article $article = null)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \AppBundle\Entity\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set push
     *
     * @param \AppBundle\Entity\Push $push
     *
     * @return Event
     */
    public function setPush(\AppBundle\Entity\Push $push = null)
    {
        $this->push = $push;

        return $this;
    }

    /**
     * Get push
     *
     * @return \AppBundle\Entity\Push
     */
    public function getPush()
    {
        return $this->push;
    }

    /**
     * Add participant
     *
     * @param \UserBundle\Entity\User $participant
     *
     * @return Event
     */
    public function addParticipant(\UserBundle\Entity\User $participant)
    {
        $this->participants[] = $participant;

        return $this;
    }

    /**
     * Remove participant
     *
     * @param \UserBundle\Entity\User $participant
     */
    public function removeParticipant(\UserBundle\Entity\User $participant)
    {
        $this->participants->removeElement($participant);
    }

    /**
     * Get participants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Add reservation
     *
     * @param \AppBundle\Entity\EventReservation $reservation
     *
     * @return Event
     */
    public function addReservation(\AppBundle\Entity\EventReservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    /**
     * Remove reservation
     *
     * @param \AppBundle\Entity\EventReservation $reservation
     */
    public function removeReservation(\AppBundle\Entity\EventReservation $reservation)
    {
        $this->reservations->removeElement($reservation);
    }

    /**
     * Get reservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return Event
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
     * Add volunteer
     *
     * @param \AppBundle\Entity\EventVolunteer $volunteer
     *
     * @return Event
     */
    public function addVolunteer(\AppBundle\Entity\EventVolunteer $volunteer)
    {
        $this->volunteers[] = $volunteer;

        return $this;
    }

    /**
     * Remove volunteer
     *
     * @param \AppBundle\Entity\EventVolunteer $volunteer
     */
    public function removeVolunteer(\AppBundle\Entity\EventVolunteer $volunteer)
    {
        $this->volunteers->removeElement($volunteer);
    }

    /**
     * Get volunteers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVolunteers()
    {
        return $this->volunteers;
    }

    /**
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Event
     */
    public function addCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \AppBundle\Entity\Category $category
     */
    public function removeCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Event
     */
    public function addImage(\AppBundle\Entity\File $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\File $image
     */
    public function removeImage(\AppBundle\Entity\File $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set participantsNbre
     *
     * @param string $participantsNbre
     *
     * @return Event
     */
    public function setParticipantsNbre($participantsNbre)
    {
        $this->participantsNbre = $participantsNbre;

        return $this;
    }

    /**
     * Get participantsNbre
     *
     * @return string
     */
    public function getParticipantsNbre()
    {
        return $this->participantsNbre;
    }

    /**
     * Add carpooling
     *
     * @param \AppBundle\Entity\Carpooling $carpooling
     *
     * @return Event
     */
    public function addCarpooling(\AppBundle\Entity\Carpooling $carpooling)
    {
        $this->carpoolings[] = $carpooling;

        return $this;
    }

    /**
     * Remove carpooling
     *
     * @param \AppBundle\Entity\Carpooling $carpooling
     */
    public function removeCarpooling(\AppBundle\Entity\Carpooling $carpooling)
    {
        $this->carpoolings->removeElement($carpooling);
    }

    /**
     * Get carpoolings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarpoolings()
    {
        return $this->carpoolings;
    }

    /**
     * Set publicAt
     *
     * @param \DateTime $publicAt
     *
     * @return Event
     */
    public function setPublicAt($publicAt)
    {
        $this->publicAt = $publicAt;

        return $this;
    }

    /**
     * Get publicAt
     *
     * @return \DateTime
     */
    public function getPublicAt()
    {
        return $this->publicAt;
    }

    /**
     * Set private
     *
     * @param boolean $private
     *
     * @return Event
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private
     *
     * @return boolean
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return Event
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

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Event
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
     * Set secondaryCommunity
     *
     * @param \AppBundle\Entity\Community $secondaryCommunity
     *
     * @return Event
     */
    public function setSecondaryCommunity(\AppBundle\Entity\Community $secondaryCommunity = null)
    {
        $this->secondaryCommunity = $secondaryCommunity;

        return $this;
    }

    /**
     * Get secondaryCommunity
     *
     * @return \AppBundle\Entity\Community
     */
    public function getSecondaryCommunity()
    {
        return $this->secondaryCommunity;
    }

    /**
     * Add secondaryCategory
     *
     * @param \AppBundle\Entity\Category $secondaryCategory
     *
     * @return Event
     */
    public function addSecondaryCategory(\AppBundle\Entity\Category $secondaryCategory)
    {
        $this->secondaryCategories[] = $secondaryCategory;

        return $this;
    }

    /**
     * Remove secondaryCategory
     *
     * @param \AppBundle\Entity\Category $secondaryCategory
     */
    public function removeSecondaryCategory(\AppBundle\Entity\Category $secondaryCategory)
    {
        $this->secondaryCategories->removeElement($secondaryCategory);
    }

    /**
     * Get secondaryCategory
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSecondaryCategories()
    {
        return $this->secondaryCategories;
    }


    

    /**
     * Set moderateSecondaryCommunity
     *
     * @param string $moderateSecondaryCommunity
     *
     * @return Event
     */
    public function setModerateSecondaryCommunity($moderateSecondaryCommunity)
    {
        $this->moderateSecondaryCommunity = $moderateSecondaryCommunity;

        return $this;
    }

    /**
     * Get moderateSecondaryCommunity
     *
     * @return string
     */
    public function getModerateSecondaryCommunity()
    {
        return $this->moderateSecondaryCommunity;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Event $parent
     *
     * @return Event
     */
    public function setParent(\AppBundle\Entity\Event $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Event
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add duplicatedEvent
     *
     * @param \AppBundle\Entity\Event $duplicatedEvent
     *
     * @return Event
     */
    public function addDuplicatedEvent(\AppBundle\Entity\Event $duplicatedEvent)
    {
        $this->duplicatedEvents[] = $duplicatedEvent;

        return $this;
    }

    /**
     * Remove duplicatedEvent
     *
     * @param \AppBundle\Entity\Event $duplicatedEvent
     */
    public function removeDuplicatedEvent(\AppBundle\Entity\Event $duplicatedEvent)
    {
        $this->duplicatedEvents->removeElement($duplicatedEvent);
    }

    /**
     * Get duplicatedEvents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDuplicatedEvents()
    {
        return $this->duplicatedEvents;
    }
}
