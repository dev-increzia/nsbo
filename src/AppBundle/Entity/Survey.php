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
 * Survey
 *
 * @ORM\Table(name="survey")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SurveyRepository")
 */
class Survey
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("title")
     * @Type("string")
     */
    private $title;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $image;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Expose
     * @SerializedName("text")
     * @Type("string")
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     * @Expose
     * @SerializedName("is_active")
     */
    private $enabled = false;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     * @SerializedName("community")
     */
    private $community;
    
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SurveyQuestion", mappedBy="survey", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $questions;
    
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
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
    }
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Add question
     *
     * @param \AppBundle\Entity\Abus $question
     *
     * @return Survey
     */
    public function addAbus(\AppBundle\Entity\SurveyQuestion $question)
    {
        $this->questions[] = $question;

        return $this;
    }

    /**
     * Remove question
     *
     * @param \AppBundle\Entity\SurveyQuestion $question
     */
    public function removeAbus(\AppBundle\Entity\SurveyQuestion $question)
    {
        $this->questions->removeElement($question);
    }

    /**
     * Get abuses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAbuses()
    {
        return $this->questions;
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
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return Survey
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
     * @return Survey
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
     * @return Survey
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
     * @return Survey
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
     * Add question
     *
     * @param \AppBundle\Entity\SurveyQuestion $question
     *
     * @return Survey
     */
    public function addQuestion(\AppBundle\Entity\SurveyQuestion $question)
    {
        $this->questions[] = $question;
        $question->setSurvey($this);

        return $this;
    }

    /**
     * Remove question
     *
     * @param \AppBundle\Entity\SurveyQuestion $question
     */
    public function removeQuestion(\AppBundle\Entity\SurveyQuestion $question)
    {
        $this->questions->removeElement($question);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }
}
