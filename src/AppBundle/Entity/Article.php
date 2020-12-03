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
 * Article
 *
 * @ORM\Table(name="article", options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArticleRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Article
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
     * @var string
     *
     * @ORM\Column(name="public_at", type="datetime", nullable=true)
     * @Expose
     * @SerializedName("publicAt")
     */
    private $publicAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     */
    private $createBy;

    /**
     * @var string
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=false)
     * @Expose
     * @SerializedName("updateAt")
     * @Type("DateTime")
     */
    private $updateAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $updateBy;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Abus", mappedBy="article", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $abuses;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("event")
     */
    private $event;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\GoodPlan", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("event")
     */
    private $goodPlan;

    /**
     * @var boolean
     * @ORM\Column(name="is_private", type="boolean")
     * @Expose
     * @SerializedName("is_private")
     */
    private $private = false;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     * @Expose
     * @SerializedName("title")
     * @Type("string")
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Category", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinTable(name="article_categories")
     * @Expose
     * @SerializedName("categories")
     */
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ArticlePublishing", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("publishing")
     */
    private $publishing;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="article_images",
     *      joinColumns={@ORM\JoinColumn(onDelete="CASCADE", name="article_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE", name="image_id", referencedColumnName="id", unique=true)}
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $document;
    
    /**
     * @var string
     * @Expose
     * @SerializedName("video_url")
     * @Type("string")
     */
    private $videoURL;

    /**
     * @var string
     * @Expose
     * @SerializedName("document_url")
     * @Type("string")
     */
    private $documentURL;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     * @Expose
     * @SerializedName("text")
     * @Type("string")
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pushEnabled", type="boolean")
     * @Expose
     * @SerializedName("pushEnabled")
     */
    private $pushEnabled = false;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Push", cascade={"persist"}, mappedBy="article")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     * @Expose
     * @SerializedName("push")
     */
    private $push;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     * @Expose
     * @SerializedName("is_active")
     */
    private $enabled = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="state", type="boolean")
     * @Expose
     */
    private $state = false;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("association")
     */
    private $association;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Merchant", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("merchant")
     */
    private $merchant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("community")
     */
    private $community;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("user")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="article", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @ORM\OrderBy({"createAt" = "ASC"})
     * @Assert\Valid()
     * @Expose
     * @SerializedName("comments")
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, columnDefinition="enum('community', 'association','merchant', 'user')", nullable=false)
     * @Expose
     * @SerializedName("type")
     */
    private $type = 'community';

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="article", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $notifications;

    /**
     * @var string
     * @Expose
     * @SerializedName("image_url")
     * @Type("string")
     */
    private $imageURL;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ArticleHeading", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("community")
     */
    private $articleHeading;

    /**
     * @var string
     * @Expose
     * @SerializedName("unread_comments")
     * @Type("string")
     */
    private $unreadComments;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArticleLikes", mappedBy="article", cascade={"persist", "remove"})
     * @Expose
     * @SerializedName("likes")
     */
    private $likes;

    /**
     * @var string
     * @Expose 
     * @SerializedName("articleComments")
     */
    private $articleComments;

    /**
     * @ORM\ManyToOne(targetEntity="Article", cascade={"persist"}, inversedBy="duplicatedArticles")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Expose
     * @SerializedName("parent")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Article", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     */
    private $duplicatedArticles;

    public function getArticleComments()
    {
        return $this->articleComments;
    }

    public function setArticleComments($articleComments)
    {
        $this->articleComments = $articleComments;
        return $this;
    }

    public function getUnreadComments()
    {
        return $this->unreadComments;
    }

    public function setUnreadComments($unreadComments)
    {
        $this->unreadComments = $unreadComments;
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

    public function getDocumentURL()
    {
        return $this->documentURL;
    }

    public function setDocumentURL($documentURL)
    {
        $this->documentURL = $documentURL;
        return $this;
    }

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
        $this->setUpdateAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->setCreateAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $this->setUpdateAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->abuses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->duplicatedArticles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Article
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
     * Set publicAt
     *
     * @param \DateTime $publicAt
     *
     * @return Article
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
     * Set updateAt
     *
     * @param \DateTime $updateAt
     *
     * @return Article
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
     * @return Article
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
     * @return Article
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
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Article
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
     * @return Article
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
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return Article
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
     * @return Article
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
     * Add abus
     *
     * @param \AppBundle\Entity\Abus $abus
     *
     * @return Article
     */
    public function addAbus(\AppBundle\Entity\Abus $abus)
    {
        $this->abuses[] = $abus;

        return $this;
    }

    /**
     * Remove abus
     *
     * @param \AppBundle\Entity\Abus $abus
     */
    public function removeAbus(\AppBundle\Entity\Abus $abus)
    {
        $this->abuses->removeElement($abus);
    }

    /**
     * Get abuses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAbuses()
    {
        return $this->abuses;
    }

    public function getAccount()
    {
        $account = null;
        if($this->getAssociation()) {
            $account = $this->getAssociation();
        } elseif ($this->getCommunity()) {
            $account = $this->getCommunity();
        }
        return $account;
    }

    /**
     * Set association
     *
     * @param \AppBundle\Entity\Association $association
     *
     * @return Article
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
     * Set merchant
     *
     * @param \AppBundle\Entity\Merchant $merchant
     *
     * @return Article
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Article
     */
    public function setUser(\UserBundle\Entity\User $user = null)
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
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Article
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
     * Set type
     *
     * @param string $type
     *
     * @return Article
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
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return Article
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Article
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
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Article
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

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Article
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
     * @return Article
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
     * @return Article
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
     * Add like
     *
     * @param \AppBundle\Entity\ArticleLikes $like
     *
     * @return Article
     */
    public function addLike(\AppBundle\Entity\ArticleLikes $like)
    {
        $this->likes[] = $like;

        return $this;
    }

    /**
     * Remove like
     *
     * @param \AppBundle\Entity\ArticleLikes $like
     */
    public function removeLike(\AppBundle\Entity\ArticleLikes $like)
    {
        $this->likes->removeElement($like);
    }

    /**
     * Get likes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * Set publishing
     *
     * @param \AppBundle\Entity\ArticlePublishing $publishing
     *
     * @return Article
     */
    public function setPublishing(\AppBundle\Entity\ArticlePublishing $publishing = null)
    {
        $this->publishing = $publishing;

        return $this;
    }

    /**
     * Get publishing
     *
     * @return \AppBundle\Entity\ArticlePublishing
     */
    public function getPublishing()
    {
        return $this->publishing;
    }

    /**
     * Set articleHeading
     *
     * @param \AppBundle\Entity\ArticleHeading $articleHeading
     *
     * @return Article
     */
    public function setArticleHeading(\AppBundle\Entity\ArticleHeading $articleHeading = null)
    {
        $this->articleHeading = $articleHeading;

        return $this;
    }

    /**
     * Get articleHeading
     *
     * @return \AppBundle\Entity\ArticleHeading
     */
    public function getArticleHeading()
    {
        return $this->articleHeading;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return Article
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
     * Set goodPlan
     *
     * @param \AppBundle\Entity\GoodPlan $goodPlan
     *
     * @return Article
     */
    public function setGoodPlan(\AppBundle\Entity\GoodPlan $goodPlan = null)
    {
        $this->goodPlan = $goodPlan;

        return $this;
    }

    /**
     * Get goodPlan
     *
     * @return \AppBundle\Entity\GoodPlan
     */
    public function getGoodPlan()
    {
        return $this->goodPlan;
    }

    /**
     * Set private
     *
     * @param boolean $private
     *
     * @return Article
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
     * Set document
     *
     * @param \AppBundle\Entity\File $document
     *
     * @return Article
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

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Article $parent
     *
     * @return Article
     */
    public function setParent(\AppBundle\Entity\Article $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Article
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add duplicatedArticle
     *
     * @param \AppBundle\Entity\Article $duplicatedArticle
     *
     * @return Article
     */
    public function addDuplicatedArticle(\AppBundle\Entity\Article $duplicatedArticle)
    {
        $this->duplicatedArticles[] = $duplicatedArticle;

        return $this;
    }

    /**
     * Remove duplicatedArticle
     *
     * @param \AppBundle\Entity\Article $duplicatedArticle
     */
    public function removeDuplicatedArticle(\AppBundle\Entity\Article $duplicatedArticle)
    {
        $this->duplicatedArticles->removeElement($duplicatedArticle);
    }

    /**
     * Get duplicatedArticles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDuplicatedArticles()
    {
        return $this->duplicatedArticles;
    }
}
