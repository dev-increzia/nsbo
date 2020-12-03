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
 * Comment
 *
 * @ORM\Table(name="comment", options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Comment
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
     * @ORM\OrderBy({"createAt" = "DESC"})
     * @Expose
     * @SerializedName("createAt")
     * @Type("DateTime")
     */
    private $createAt;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $document;

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
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     * @Expose
     * @SerializedName("content")
     * @Type("string")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @SerializedName("user")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("association")
     */
    private $association;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Merchant", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("merchant")
     */
    private $merchant;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GoodPlan", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("good_plan")
     */
    private $goodPlan;

    /**
     * @var string
     * @Expose
     * @ORM\Column(name="type", type="string", length=255, columnDefinition="enum('citizen', 'association','merchant')", nullable=false)
     */
    private $type = 'citizen';

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Article", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Expose
     * @SerializedName("article")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Expose
     * @SerializedName("event")
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Comment", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @ORM\OrderBy({"createAt" = "ASC"})
     * @Assert\Valid()
     * @Expose
     * @SerializedName("replies")
     */
    private $comments;
    
    
    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User", mappedBy="commentsRead", cascade={"persist"})
     */
    private $usersRead;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $image;

    /**
     * @var boolean
     *
     * @ORM\Column(name="readed", type="boolean")
     * @Expose
     */
    private $readed = false;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="comment", cascade={"persist"})
     */
    private $notifications;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     */
    private $community;

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
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersRead = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Comment
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
     * @return Comment
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
     * Set content
     *
     * @param string $content
     *
     * @return Comment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Comment
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
     * Set readed
     *
     * @param boolean $readed
     *
     * @return Comment
     */
    public function setReaded($readed)
    {
        $this->readed = $readed;

        return $this;
    }

    /**
     * Get readed
     *
     * @return boolean
     */
    public function getReaded()
    {
        return $this->readed;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Comment
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
     * Set association
     *
     * @param \AppBundle\Entity\Association $association
     *
     * @return Comment
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
     * @return Comment
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
     * Set article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return Comment
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Comment
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
     * Set parent
     *
     * @param \AppBundle\Entity\Comment $parent
     *
     * @return Comment
     */
    public function setParent(\AppBundle\Entity\Comment $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Comment
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Comment
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
     * Add usersRead
     *
     * @param \UserBundle\Entity\User $usersRead
     *
     * @return Comment
     */
    public function addUsersRead(\UserBundle\Entity\User $usersRead)
    {
        $this->usersRead[] = $usersRead;

        return $this;
    }

    /**
     * Remove usersRead
     *
     * @param \UserBundle\Entity\User $usersRead
     */
    public function removeUsersRead(\UserBundle\Entity\User $usersRead)
    {
        $this->usersRead->removeElement($usersRead);
    }

    /**
     * Get usersRead
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsersRead()
    {
        return $this->usersRead;
    }

    /**
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return Comment
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
     * Set image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return Comment
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
     * Set goodPlan
     *
     * @param \AppBundle\Entity\GoodPlan $goodPlan
     *
     * @return Comment
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
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return Comment
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
     * Set document
     *
     * @param \AppBundle\Entity\File $document
     *
     * @return Comment
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
