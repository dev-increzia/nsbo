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
 * GoodPlan
 *
 * @ORM\Table(name="good_plan", options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GoodPlanRepository")
 */
class GoodPlan
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
     *
     * @ORM\Column(name="public_at", type="datetime", nullable=true)
     * @Expose
     * @SerializedName("publicAt")
     */
    private $publicAt;

    /**
     * @var string
     * @Expose
     * @SerializedName("title")
     * @ORM\Column(name="title", type="string", length=255, nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     */
    private $title;

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
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinTable(name="good_plan_images",
     *      joinColumns={@ORM\JoinColumn(name="good_plan_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $images;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Merchant", cascade={"persist"}, inversedBy="goodPlans")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("merchant")
     */
    private $merchant;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pushEnabled", type="boolean")
     * @Expose
     * @SerializedName("pushEnabled")
     */
    private $pushEnabled = false;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Push", cascade={"persist"}, mappedBy="goodPlan")
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="goodPlan", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @ORM\OrderBy({"createAt" = "ASC"})
     * @Assert\Valid()
     * @Expose
     * @SerializedName("comments")
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Category", inversedBy="events", cascade={"persist"})
     * @ORM\JoinTable(name="good_plan_categories")
     * @Expose
     * @SerializedName("categories")
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="goodPlan", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $notifications;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Article", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("article")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("community")
     */
    private $community;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("secondary_community")
     */
    private $secondaryCommunity;

    /**
     * @var boolean
     *
     * @ORM\Column(name="moderate_secondary_community", type="string", length=255, columnDefinition="enum('wait', 'accepted', 'refuse')", nullable=false)
     */
    private $moderateSecondaryCommunity = 'wait';

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Category", inversedBy="secondaryCategories", cascade={"persist"})
     * @ORM\JoinTable(name="good_plan_secondary_categories")
     */
    private $secondaryCategories;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City", cascade={"persist"}, inversedBy="events")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $city;

    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User", inversedBy="goodPlanParticipants", cascade={"persist"})
     * @ORM\JoinTable(name="goodplan_participants")
     * @Expose
     * @SerializedName("participants")
     */
    private $participants;

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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $document;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->participants = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return GoodPlan
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
     * @return GoodPlan
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
     * Set publicAt
     *
     * @param \DateTime $publicAt
     *
     * @return GoodPlan
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
     * Set title
     *
     * @param string $title
     *
     * @return GoodPlan
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
     * @return GoodPlan
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
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return GoodPlan
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
     * @return GoodPlan
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
     * @return GoodPlan
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
     * Set pushEnabled
     *
     * @param boolean $pushEnabled
     *
     * @return GoodPlan
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
     * @return GoodPlan
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
     * Set private
     *
     * @param boolean $private
     *
     * @return GoodPlan
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
     * Set state
     *
     * @param boolean $state
     *
     * @return GoodPlan
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
     * Set moderate
     *
     * @param string $moderate
     *
     * @return GoodPlan
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
     * @return GoodPlan
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
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return GoodPlan
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
     * @return GoodPlan
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
     * Add image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return GoodPlan
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
     * Set image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return GoodPlan
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
     * Set merchant
     *
     * @param \AppBundle\Entity\Merchant $merchant
     *
     * @return GoodPlan
     */
    public function setMerchant(\AppBundle\Entity\Merchant $merchant = null)
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

    /**
     * Set push
     *
     * @param \AppBundle\Entity\Push $push
     *
     * @return GoodPlan
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
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return GoodPlan
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
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return GoodPlan
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
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return GoodPlan
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
     * Set article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return GoodPlan
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
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return GoodPlan
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
     * Set secondaryCommunity
     *
     * @param \AppBundle\Entity\Community $secondaryCommunity
     *
     * @return GoodPlan
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
     * @return GoodPlan
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
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return GoodPlan
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
     * Add participant
     *
     * @param \UserBundle\Entity\User $participant
     *
     * @return GoodPlan
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
     * Set moderateSecondaryCommunity
     *
     * @param string $moderateSecondaryCommunity
     *
     * @return GoodPlan
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
     * Set document
     *
     * @param \AppBundle\Entity\File $document
     *
     * @return GoodPlan
     */
    public function setDocument(\AppBundle\Entity\File $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \AppBundle\Entity\File
     */
    public function getDocument()
    {
        return $this->document;
    }
}
