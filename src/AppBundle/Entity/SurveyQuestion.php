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
 * SurveyQuestion
 *
 * @ORM\Table(name="survey_question")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SurveyQuestionRepository")
 */
class SurveyQuestion
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Survey", cascade={"persist"}, inversedBy="questions")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $survey;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SurveyQuestionChoice", mappedBy="question", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $choices;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->choices = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return SurveyQuestion
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
     * Set survey
     *
     * @param \AppBundle\Entity\Survey $survey
     *
     * @return SurveyQuestion
     */
    public function setSurvey(\AppBundle\Entity\Survey $survey = null)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey
     *
     * @return \AppBundle\Entity\Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

    /**
     * Add choice
     *
     * @param \AppBundle\Entity\SurveyQuestionChoice $choice
     *
     * @return SurveyQuestion
     */
    public function addChoice(\AppBundle\Entity\SurveyQuestionChoice $choice)
    {
        $this->choices[] = $choice;
        $choice->setQuestion($this);

        return $this;
    }

    /**
     * Remove choice
     *
     * @param \AppBundle\Entity\SurveyQuestionChoice $choice
     */
    public function removeChoice(\AppBundle\Entity\SurveyQuestionChoice $choice)
    {
        $this->choices->removeElement($choice);
    }

    /**
     * Get choices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoices()
    {
        return $this->choices;
    }
}
