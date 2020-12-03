<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReportingObjectHeading
 *
 * @ORM\Table(name="reporting_category_heading")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportingObjectHeadingRepository")
 */
class ReportingObjectHeading
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
     * @ORM\Column(name="objet", type="string", length=255)
     */
    private $objet;

    /**
     *
     * @var string
     *
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",
     *     message="Email invalide"
     * )
     * @ORM\Column(name="recipient", type="string", length=255)
     */
    private $recipient;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReportingHeading", cascade={"persist"}, inversedBy="objects")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $reportingHeading;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reporting", mappedBy="reportingObjectHeading", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $reportings;


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
     * Set objet
     *
     * @param string $objet
     *
     * @return ReportingObjectHeading
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set recipient
     *
     * @param string $recipient
     *
     * @return ReportingObjectHeading
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set reportingHeading
     *
     * @param \AppBundle\Entity\ReportingHeading $reportingHeading
     *
     * @return ReportingObjectHeading
     */
    public function setReportingHeading(\AppBundle\Entity\ReportingHeading $reportingHeading = null)
    {
        $this->reportingHeading = $reportingHeading;

        return $this;
    }

    /**
     * Get reportingHeading
     *
     * @return \AppBundle\Entity\ReportingHeading
     */
    public function getReportingHeading()
    {
        return $this->reportingHeading;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reportings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reporting
     *
     * @param \AppBundle\Entity\Reporting $reporting
     *
     * @return ReportingObjectHeading
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
}
