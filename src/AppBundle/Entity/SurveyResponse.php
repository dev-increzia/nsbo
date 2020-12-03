<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use UserBundle\Entity\User;

/**
 * SurveyResponse
 *
 * @ORM\Table(name="survey_response")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SurveyResponseRepository")
 */
class SurveyResponse
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SurveyQuestionChoice", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $response;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="added_at", type="datetime", nullable=true)
     */
    private $addedAt;


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
     * Set response
     *
     * @param string $response
     *
     * @return SurveyResponse
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return SurveyQuestionChoice
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set user
     *
     * @param string $user
     *
     * @return SurveyResponse
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set addedAt
     *
     * @param \DateTime $addedAt
     *
     * @return SurveyResponse
     */
    public function setAddedAt($addedAt)
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    /**
     * Get addedAt
     *
     * @return \DateTime
     */
    public function getAddedAt()
    {
        return $this->addedAt;
    }
}
