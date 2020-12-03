<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SurveyQuestionChoice
 *
 * @ORM\Table(name="survey_question_choice")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SurveyQuestionChoiceRepository")
 */
class SurveyQuestionChoice
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SurveyQuestion", cascade={"persist"}, inversedBy="choices")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SurveyResponse", mappedBy="response", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     */
    private $responses;


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
     * @return SurveyQuestionChoice
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
     * Set question
     *
     * @param \AppBundle\Entity\SurveyQuestion $question
     *
     * @return SurveyQuestionChoice
     */
    public function setQuestion(\AppBundle\Entity\SurveyQuestion $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \AppBundle\Entity\SurveyQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->responses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add response
     *
     * @param \AppBundle\Entity\SurveyResponse $response
     *
     * @return SurveyQuestionChoice
     */
    public function addResponse(\AppBundle\Entity\SurveyResponse $response)
    {
        $this->responses[] = $response;

        return $this;
    }

    /**
     * Remove response
     *
     * @param \AppBundle\Entity\SurveyResponse $response
     */
    public function removeResponse(\AppBundle\Entity\SurveyResponse $response)
    {
        $this->responses->removeElement($response);
    }

    /**
     * Get responses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResponses()
    {
        return $this->responses;
    }
}
