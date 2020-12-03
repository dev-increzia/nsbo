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
 * Community
 *
 * @ORM\Table(name="community")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommunityRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Community
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
     * @ORM\Column(name="is_private", type="boolean")
     */
    private $isPrivate = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_comment_active", type="boolean")
     */
    private $isCommentActive = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_comment_article_heading_active", type="boolean")
     */
    private $isCommentArticleHeadingActive = true;



    /**
     * @var string
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     */
    private $createAt;

    /**
     * @var string
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=false)
     */
    private $updateAt;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City", cascade={"persist"}, inversedBy="communities")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("city")
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\User", mappedBy="communityAdmin", cascade={"persist"})
     */
    private $admins;

    /**
     * @ORM\OneToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"}, mappedBy="communitySuAdmin")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     *
     */
    private $suAdmin;

    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User", inversedBy="adminCommunities" ,cascade={"persist"})
     * @ORM\JoinTable(name="community_admins")
     */
    private $communityAdmins;

    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User",inversedBy="suAdminCommunities" ,cascade={"persist"})
     * @ORM\JoinTable(name="community_su_admins")
     */
    private $communitySuadmins;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AccessAdminCommunity", mappedBy="community", cascade={"persist"})
     * @Expose
     *
     */
    private $access;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CommunitySetting", cascade={"persist"})
     * @ORM\JoinTable(name="community_settings")
     */
    private $settings;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MapHeading", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */

    private $mapHeadings;
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportingHeading", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $reportingHeadings;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArticleHeading", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $articleHeadings;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Category", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("password")
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="expiration_date", type="date", nullable=true)
     */
    private $expirationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="help_page_content", type="text",  nullable=true)
     */
    private $helpPageContent;

    /**
     * @var string
     *
     * @ORM\Column(name="presentation_description", type="text",  nullable=true)
     */
    private $presentationDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="presentation_title", type="string", length=255,  nullable=true)
     */
    private $presentationTitle;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $image;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $presentationImage;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Article", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Push", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $pushs;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PhoneBookHeading", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $phonebookHeadings;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UsefullLinkHeading", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $usefullLinkHeadings;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Association", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $associations;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Merchant", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $merchants;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reporting", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $reportings;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CommunityUsers", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $users;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(name="gaApplication", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("gaApplication")
     */
    private $gaApplication;

    /**
     * @var string
     *
     * @ORM\Column(name="gaApplicationProfileIDMOBILE", type="string", length=255, nullable=true)
     */
    private $gaApplicationProfileIDMOBILE;

    /**
     * @var string
     *
     * @ORM\Column(name="gaApplicationProfileIDWEB", type="string", length=255, nullable=true)
     */
    private $gaApplicationProfileIDWEB;

    /**
     * @var string
     *
     * @ORM\Column(name="gaBackoffice", type="string", length=255, nullable=true)
     */
    private $gaBackoffice;

    /**
     * @var string
     *
     * @ORM\Column(name="gaBackofficeProfileID", type="string", length=255, nullable=true)
     */
    private $gaBackofficeProfileID;

    /**
     * @var boolean
     *
     * @ORM\Column(name="auto_mod_good_plan", type="boolean")
     */
    private $autoModGoodPlan = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="auto_mod_event", type="boolean")
     */
    private $autoModEvent = false;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinTable(name="community_images",
     *      joinColumns={@ORM\JoinColumn(name="community_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $images;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
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
        $this->admins = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pushs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->numbers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->associations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->merchants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reportings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new\Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->passwords = new \Doctrine\Common\Collections\ArrayCollection();
        $this->settings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->communityAdmins = new \Doctrine\Common\Collections\ArrayCollection();
        $this->communitySuadmins = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Community
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
     * @return Community
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
     * @return Community
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
     * Set email
     *
     * @param string $email
     *
     * @return Community
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
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Community
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
     * Set color
     *
     * @param string $color
     *
     * @return Community
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return Community
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
     * Add admin
     *
     * @param \UserBundle\Entity\User $admin
     *
     * @return Community
     */
    public function addAdmin(\UserBundle\Entity\User $admin)
    {
        $this->admins[] = $admin;

        return $this;
    }

    /**
     * Remove admin
     *
     * @param \UserBundle\Entity\User $admin
     */
    public function removeAdmin(\UserBundle\Entity\User $admin)
    {
        $this->admins->removeElement($admin);
    }

    /**
     * Get admins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdmins()
    {
        return $this->admins;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Community
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
     * Add event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Community
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
     * Remove push
     *
     * @param \AppBundle\Entity\Push $push
     */
    public function removePush(\AppBundle\Entity\Push $push)
    {
        $this->pushs->removeElement($push);
    }

    /**
     * Get pushs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPushs()
    {
        return $this->pushs;
    }

    /**
     * Add number
     *
     * @param \AppBundle\Entity\Number $number
     *
     * @return Community
     */
    public function addNumber(\AppBundle\Entity\Number $number)
    {
        $this->numbers[] = $number;

        return $this;
    }

    /**
     * Remove number
     *
     * @param \AppBundle\Entity\Number $number
     */
    public function removeNumber(\AppBundle\Entity\Number $number)
    {
        $this->numbers->removeElement($number);
    }

    /**
     * Get numbers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * Add association
     *
     * @param \AppBundle\Entity\Association $association
     *
     * @return Community
     */
    public function addAssociation(\AppBundle\Entity\Association $association)
    {
        $this->associations[] = $association;

        return $this;
    }

    /**
     * Remove association
     *
     * @param \AppBundle\Entity\Association $association
     */
    public function removeAssociation(\AppBundle\Entity\Association $association)
    {
        $this->associations->removeElement($association);
    }

    /**
     * Get associations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Add merchant
     *
     * @param \AppBundle\Entity\Merchant $merchant
     *
     * @return Community
     */
    public function addMerchant(\AppBundle\Entity\Merchant $merchant)
    {
        $this->merchants[] = $merchant;

        return $this;
    }

    /**
     * Remove merchant
     *
     * @param \AppBundle\Entity\Merchant $merchant
     */
    public function removeMerchant(\AppBundle\Entity\Merchant $merchant)
    {
        $this->merchants->removeElement($merchant);
    }

    /**
     * Get merchants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMerchants()
    {
        return $this->merchants;
    }

    /**
     * Add reporting
     *
     * @param \AppBundle\Entity\Reporting $reporting
     *
     * @return Community
     */
    public function addReporting(\AppBundle\Entity\Reporting $reporting)
    {
        $this->reportings[] = $reporting;

        return $this;
    }

    /**
     * Remove reporting
     *
     * @param \AppBundle\Entity\Reporting $reporting
     */
    public function removeReporting(\AppBundle\Entity\Reporting $reporting)
    {
        $this->reportings->removeElement($reporting);
    }

    /**
     * Get reportings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReportings()
    {
        return $this->reportings;
    }


    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Community
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
     * Set gaApplication
     *
     * @param string $gaApplication
     *
     * @return Community
     */
    public function setGaApplication($gaApplication)
    {
        $this->gaApplication = $gaApplication;

        return $this;
    }

    /**
     * Get gaApplication
     *
     * @return string
     */
    public function getGaApplication()
    {
        return $this->gaApplication;
    }

    /**
     * Set gaApplicationProfileIDMOBILE
     *
     * @param string $gaApplicationProfileIDMOBILE
     *
     * @return Community
     */
    public function setGaApplicationProfileIDMOBILE($gaApplicationProfileIDMOBILE)
    {
        $this->gaApplicationProfileIDMOBILE = $gaApplicationProfileIDMOBILE;

        return $this;
    }

    /**
     * Get gaApplicationProfileIDMOBILE
     *
     * @return string
     */
    public function getGaApplicationProfileIDMOBILE()
    {
        return $this->gaApplicationProfileIDMOBILE;
    }

    /**
     * Set gaApplicationProfileIDWEB
     *
     * @param string $gaApplicationProfileIDWEB
     *
     * @return Community
     */
    public function setGaApplicationProfileIDWEB($gaApplicationProfileIDWEB)
    {
        $this->gaApplicationProfileIDWEB = $gaApplicationProfileIDWEB;

        return $this;
    }

    /**
     * Get gaApplicationProfileIDWEB
     *
     * @return string
     */
    public function getGaApplicationProfileIDWEB()
    {
        return $this->gaApplicationProfileIDWEB;
    }

    /**
     * Set gaBackoffice
     *
     * @param string $gaBackoffice
     *
     * @return Community
     */
    public function setGaBackoffice($gaBackoffice)
    {
        $this->gaBackoffice = $gaBackoffice;

        return $this;
    }

    /**
     * Get gaBackoffice
     *
     * @return string
     */
    public function getGaBackoffice()
    {
        return $this->gaBackoffice;
    }

    /**
     * Set gaBackofficeProfileID
     *
     * @param string $gaBackofficeProfileID
     *
     * @return Community
     */
    public function setGaBackofficeProfileID($gaBackofficeProfileID)
    {
        $this->gaBackofficeProfileID = $gaBackofficeProfileID;

        return $this;
    }

    /**
     * Get gaBackofficeProfileID
     *
     * @return string
     */
    public function getGaBackofficeProfileID()
    {
        return $this->gaBackofficeProfileID;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Community
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Add article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return Community
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
     * Add push
     *
     * @param \AppBundle\Entity\Push $push
     *
     * @return Community
     */
    public function addPush(\AppBundle\Entity\Push $push)
    {
        $this->pushs[] = $push;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set expirationDate
     *
     * @param \DateTime $expirationDate
     *
     * @return Community
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate
     *
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Add phonebookHeading
     *
     * @param \AppBundle\Entity\PhoneBookHeading $phonebookHeading
     *
     * @return Community
     */
    public function addPhonebookHeading(\AppBundle\Entity\PhoneBookHeading $phonebookHeading)
    {
        $this->phonebookHeadings[] = $phonebookHeading;

        return $this;
    }

    /**
     * Remove phonebookHeading
     *
     * @param \AppBundle\Entity\PhoneBookHeading $phonebookHeading
     */
    public function removePhonebookHeading(\AppBundle\Entity\PhoneBookHeading $phonebookHeading)
    {
        $this->phonebookHeadings->removeElement($phonebookHeading);
    }

    /**
     * Get phonebookHeadings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhonebookHeadings()
    {
        return $this->phonebookHeadings;
    }

    /**
     * Set isPrivate
     *
     * @param boolean $isPrivate
     *
     * @return Community
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    /**
     * Get isPrivate
     *
     * @return boolean
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * Add access
     *
     * @param \AppBundle\Entity\AccessAdminCommunity $access
     *
     * @return Community
     */
    public function addAccess(\AppBundle\Entity\AccessAdminCommunity $access)
    {
        $this->access[] = $access;

        return $this;
    }

    /**
     * Remove access
     *
     * @param \AppBundle\Entity\AccessAdminCommunity $access
     */
    public function removeAccess(\AppBundle\Entity\AccessAdminCommunity $access)
    {
        $this->access->removeElement($access);
    }

    /**
     * Get access
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set suAdmin
     *
     * @param \UserBundle\Entity\User $suAdmin
     *
     * @return Community
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
     * Set phone
     *
     * @param string $phone
     *
     * @return Community
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
     * Add setting
     *
     * @param \AppBundle\Entity\CommunitySetting $setting
     *
     * @return Community
     */
    public function addSetting(\AppBundle\Entity\CommunitySetting $setting)
    {
        $this->settings[] = $setting;

        return $this;
    }

    /**
     * Remove setting
     *
     * @param \AppBundle\Entity\CommunitySetting $setting
     */
    public function removeSetting(\AppBundle\Entity\CommunitySetting $setting)
    {
        $this->settings->removeElement($setting);
    }

    /**
     * Get settings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function hasSetting($slug)
    {
        foreach ($this->settings as $setting) {
            if ($setting->getSlug() == $slug) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set helpPageContent
     *
     * @param string $helpPageContent
     *
     * @return Community
     */
    public function setHelpPageContent($helpPageContent)
    {
        $this->helpPageContent = $helpPageContent;

        return $this;
    }

    /**
     * Get helpPageContent
     *
     * @return string
     */
    public function getHelpPageContent()
    {
        return $this->helpPageContent;
    }

    /**
     * Set presentationTitle
     *
     * @param string $presentationTitle
     *
     * @return Community
     */
    public function setPresentationTitle($presentationTitle)
    {
        $this->presentationTitle = $presentationTitle;

        return $this;
    }

    /**
     * Get presentationTitle
     *
     * @return string
     */
    public function getPresentationTitle()
    {
        return $this->presentationTitle;
    }

    /**
     * Set presentationDescription
     *
     * @param string $presentationDescription
     *
     * @return Community
     */
    public function setPresentationDescription($presentationDescription)
    {
        $this->presentationDescription = $presentationDescription;

        return $this;
    }

    /**
     * Get presentationDescription
     *
     * @return string
     */
    public function getPresentationDescription()
    {
        return $this->presentationDescription;
    }

    /**
     * Set presentationImage
     *
     * @param \AppBundle\Entity\File $presentationImage
     *
     * @return Community
     */
    public function setPresentationImage(\AppBundle\Entity\File $presentationImage = null)
    {
        $this->presentationImage = $presentationImage;

        return $this;
    }

    /**
     * Get presentationImage
     *
     * @return \AppBundle\Entity\File
     */
    public function getPresentationImage()
    {
        return $this->presentationImage;
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\CommunityUsers $user
     *
     * @return Community
     */
    public function addUser(\AppBundle\Entity\CommunityUsers $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\CommunityUsers $user
     */
    public function removeUser(\AppBundle\Entity\CommunityUsers $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add mapHeading
     *
     * @param \AppBundle\Entity\MapHeading $mapHeading
     *
     * @return Community
     */
    public function addMapHeading(\AppBundle\Entity\MapHeading $mapHeading)
    {
        $this->mapHeadings[] = $mapHeading;

        return $this;
    }

    /**
     * Remove mapHeading
     *
     * @param \AppBundle\Entity\MapHeading $mapHeading
     */
    public function removeMapHeading(\AppBundle\Entity\MapHeading $mapHeading)
    {
        $this->mapHeadings->removeElement($mapHeading);
    }

    /**
     * Get mapHeadings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMapHeadings()
    {
        return $this->mapHeadings;
    }

    /**
     * Set isCommentActive
     *
     * @param boolean $isCommentActive
     *
     * @return Community
     */
    public function setIsCommentActive($isCommentActive)
    {
        $this->isCommentActive = $isCommentActive;

        return $this;
    }

    /**
     * Get isCommentActive
     *
     * @return boolean
     */
    public function getIsCommentActive()
    {
        return $this->isCommentActive;
    }

    /**
     * Add reportingHeading
     *
     * @param \AppBundle\Entity\ReportingHeading $reportingHeading
     *
     * @return Community
     */
    public function addreportingHeading(\AppBundle\Entity\ReportingHeading $reportingHeading)
    {
        $this->reportingHeadings[] = $reportingHeading;

        return $this;
    }

    /**
     * Remove reportingHeading
     *
     * @param \AppBundle\Entity\ReportingHeading $reportingHeading
     */
    public function removereportingHeading(\AppBundle\Entity\ReportingHeading $reportingHeading)
    {
        $this->reportingHeadings->removeElement($reportingHeading);
    }

    /**
     * Get reportingHeadings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getreportingHeadings()
    {
        return $this->reportingHeadings;
    }

    public function havePredefinedObjects()
    {
        foreach ($this->getPhonebookHeadings() as $pbh) {
            foreach ($pbh->getObjects() as $object) {
                if ($object->getName() == 'Commerces/Partenaires' || $object->getName() == 'Groupes/Associations') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Add usefullLinkHeading
     *
     * @param \AppBundle\Entity\UsefullLinkHeading $usefullLinkHeading
     *
     * @return Community
     */
    public function addUsefullLinkHeading(\AppBundle\Entity\UsefullLinkHeading $usefullLinkHeading)
    {
        $this->usefullLinkHeadings[] = $usefullLinkHeading;

        return $this;
    }

    /**
     * Remove usefullLinkHeading
     *
     * @param \AppBundle\Entity\UsefullLinkHeading $usefullLinkHeading
     */
    public function removeUsefullLinkHeading(\AppBundle\Entity\UsefullLinkHeading $usefullLinkHeading)
    {
        $this->usefullLinkHeadings->removeElement($usefullLinkHeading);
    }

    /**
     * Get usefullLinkHeadings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsefullLinkHeadings()
    {
        return $this->usefullLinkHeadings;
    }

    /**
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Community
     */
    public function addCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories[] = $category;
        $category->setCommunity($this);

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
     * Set autoModGoodPlan
     *
     * @param boolean $autoModGoodPlan
     *
     * @return Community
     */
    public function setAutoModGoodPlan($autoModGoodPlan)
    {
        $this->autoModGoodPlan = $autoModGoodPlan;

        return $this;
    }

    /**
     * Get autoModGoodPlan
     *
     * @return boolean
     */
    public function getAutoModGoodPlan()
    {
        return $this->autoModGoodPlan;
    }

    /**
     * Set autoModEvent
     *
     * @param boolean $autoModEvent
     *
     * @return Community
     */
    public function setAutoModEvent($autoModEvent)
    {
        $this->autoModEvent = $autoModEvent;

        return $this;
    }

    /**
     * Get autoModEvent
     *
     * @return boolean
     */
    public function getAutoModEvent()
    {
        return $this->autoModEvent;
    }

    /**
     * Add articleHeading
     *
     * @param \AppBundle\Entity\ArticleHeading $articleHeading
     *
     * @return Community
     */
    public function addArticleHeading(\AppBundle\Entity\ArticleHeading $articleHeading)
    {
        $this->articleHeadings[] = $articleHeading;

        return $this;
    }

    /**
     * Remove articleHeading
     *
     * @param \AppBundle\Entity\ArticleHeading $articleHeading
     */
    public function removeArticleHeading(\AppBundle\Entity\ArticleHeading $articleHeading)
    {
        $this->articleHeadings->removeElement($articleHeading);
    }

    /**
     * Get articleHeadings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticleHeadings()
    {
        return $this->articleHeadings;
    }

    /**
     * Set isCommentArticleHeadingActive
     *
     * @param boolean $isCommentArticleHeadingActive
     *
     * @return Community
     */
    public function setIsCommentArticleHeadingActive($isCommentArticleHeadingActive)
    {
        $this->isCommentArticleHeadingActive = $isCommentArticleHeadingActive;

        return $this;
    }

    /**
     * Get isCommentArticleHeadingActive
     *
     * @return boolean
     */
    public function getIsCommentArticleHeadingActive()
    {
        return $this->isCommentArticleHeadingActive;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Community
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
     * Set video
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Community
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
     * Add communityAdmin
     *
     * @param \UserBundle\Entity\User $communityAdmin
     *
     * @return Community
     */
    public function addCommunityAdmin(\UserBundle\Entity\User $communityAdmin)
    {
        $this->communityAdmins[] = $communityAdmin;

        return $this;
    }

    /**
     * Remove communityAdmin
     *
     * @param \UserBundle\Entity\User $communityAdmin
     */
    public function removeCommunityAdmin(\UserBundle\Entity\User $communityAdmin)
    {
        $this->communityAdmins->removeElement($communityAdmin);
    }

    /**
     * Get communityAdmins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommunityAdmins()
    {
        return $this->communityAdmins;
    }

    /**
     * Add communitySuadmin
     *
     * @param \UserBundle\Entity\User $communitySuadmin
     *
     * @return Community
     */
    public function addCommunitySuadmin(\UserBundle\Entity\User $communitySuadmin)
    {
        $this->communitySuadmins[] = $communitySuadmin;

        return $this;
    }

    /**
     * Remove communitySuadmin
     *
     * @param \UserBundle\Entity\User $communitySuadmin
     */
    public function removeCommunitySuadmin(\UserBundle\Entity\User $communitySuadmin)
    {
        $this->communitySuadmins->removeElement($communitySuadmin);
    }

    /**
     * Get communitySuadmins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommunitySuadmins()
    {
        return $this->communitySuadmins;
    }

}
