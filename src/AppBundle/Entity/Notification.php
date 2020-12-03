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
 * Push
 *
 * @ORM\Table(name="notification", options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Notification
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @SerializedName("id")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     * @Expose
     * @SerializedName("createAt")
     */
    private $createAt;

    /**
     * @var string
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=false)
     */
    private $updateAt;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     * @Expose
     * @SerializedName("message")
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, columnDefinition="enum('event', 'article', 'newComment', 'eventParticipateAdd', 'eventParticipateRemove', 'volunteer', 'reporting', 'eventDisabled', 'admin', 'association', 'associationRefused', 'merchant', 'merchantRefused',  'replyComment', 'goodPlan')", nullable=false)
     * @Expose
     * @SerializedName("type")
     */
    private $type = 'event';

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"}, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", cascade={"persist"}, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     */
    private $association;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Merchant", cascade={"persist"}, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     */
    private $merchant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event", cascade={"persist"}, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *  @Expose
     *  @SerializedName("event")
     */
    private $event;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GoodPlan", cascade={"persist"}, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *  @Expose
     *  @SerializedName("good_plan")
     */
    private $goodPlan;

    /**
     * @var boolean
     *
     * @ORM\Column(name="participants_informed", type="boolean")
     * @Expose
     * @SerializedName("informed")
     */
    private $participantsInformed = false;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Article", cascade={"persist"}, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("article")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Comment", cascade={"persist"}, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("comment")
     */
    private $comment;

    /**
     * @var string
     * @Expose
     * @SerializedName("tag")
     * @Type("string")
     */
    private $tag;

    /**
     * @var boolean
     *
     * @ORM\Column(name="seen", type="boolean")
     * @SerializedName("seen")
     * @Expose
     */
    private $seen = false;

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
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
     * @return Notification
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
     * @return Notification
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
     * Set message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Notification
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Notification
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Notification
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
     * Set article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return Notification
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
     * Set seen
     *
     * @param boolean $seen
     *
     * @return Notification
     */
    public function setSeen($seen)
    {
        $this->seen = $seen;

        return $this;
    }

    /**
     * Get seen
     *
     * @return boolean
     */
    public function getSeen()
    {
        return $this->seen;
    }

    /**
     * Set comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return Notification
     */
    public function setComment(\AppBundle\Entity\Comment $comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \AppBundle\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set association
     *
     * @param \AppBundle\Entity\Association $association
     *
     * @return Notification
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
     * @return Notification
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
     * Set participantsInformed
     *
     * @param boolean $participantsInformed
     *
     * @return Notification
     */
    public function setParticipantsInformed($participantsInformed)
    {
        $this->participantsInformed = $participantsInformed;

        return $this;
    }

    /**
     * Get participantsInformed
     *
     * @return boolean
     */
    public function getParticipantsInformed()
    {
        return $this->participantsInformed;
    }


    /**
     * Set goodPlan
     *
     * @param \AppBundle\Entity\GoodPlan $goodPlan
     *
     * @return Notification
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
}
