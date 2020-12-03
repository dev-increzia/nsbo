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
 * @ORM\Table(name="push", options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PushRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Push
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
     * @ORM\Column(name="sendAt", type="datetime", nullable=true)
     * @Expose
     * @SerializedName("sendAt")
     */
    private $sendAt;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true, options={"charset":"utf8mb4", "collate":"utf8mb4_unicode_ci"})
     * @Assert\Length(max = 200)
     * @Expose
     * @SerializedName("content")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="pushs")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $community;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event", cascade={"persist"}, inversedBy="push")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $event;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Article", cascade={"persist"}, inversedBy="push")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $article;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\GoodPlan", inversedBy="push")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $goodPlan;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, columnDefinition="enum('community', 'event', 'article', 'goodPlan')", nullable=false)
     */
    private $type = 'event';

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PushLog", mappedBy="push", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $pushLogs;

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
        $this->pushLogs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Push
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
     * @return Push
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
     * Set sendAt
     *
     * @param \DateTime $sendAt
     *
     * @return Push
     */
    public function setSendAt($sendAt)
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    /**
     * Get sendAt
     *
     * @return \DateTime
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Push
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
     * @return Push
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
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return Push
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
     * @return Push
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Push
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
     * @return Push
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
     * Add pushLog
     *
     * @param \AppBundle\Entity\Push $pushLog
     *
     * @return Push
     */
    public function addPushLog(\AppBundle\Entity\Push $pushLog)
    {
        $this->pushLogs[] = $pushLog;

        return $this;
    }

    /**
     * Remove pushLog
     *
     * @param \AppBundle\Entity\Push $pushLog
     */
    public function removePushLog(\AppBundle\Entity\Push $pushLog)
    {
        $this->pushLogs->removeElement($pushLog);
    }

    /**
     * Get pushLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPushLogs()
    {
        return $this->pushLogs;
    }


    /**
     * Set goodPlan
     *
     * @param \AppBundle\Entity\GoodPlan $goodPlan
     *
     * @return Push
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
