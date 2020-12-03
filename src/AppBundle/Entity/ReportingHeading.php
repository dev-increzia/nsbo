<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReportingHeading
 *
 * @ORM\Table(name="reporting_heading")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportingHeadingRepository")
 */
class ReportingHeading extends AbstractHeading
{
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportingObjectHeading", mappedBy="reportingHeading", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $objects;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="reportingHeadings",cascade={"persist"})
     * @ORM\JoinColumn(name="community_id",nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     */
    private $community;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->objects = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add object
     *
     * @param \AppBundle\Entity\ReportingObjectHeading $object
     *
     * @return ReportingHeading
     */
    public function addObject(\AppBundle\Entity\ReportingObjectHeading $object)
    {
        $this->objects[] = $object;

        return $this;
    }

    /**
     * Remove object
     *
     * @param \AppBundle\Entity\ReportingObjectHeading $object
     */
    public function removeObject(\AppBundle\Entity\ReportingObjectHeading $object)
    {
        $this->objects->removeElement($object);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return ReportingHeading
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
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return ReportingHeading
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

    public function getObject(\AppBundle\Entity\ReportingObjectHeading $object)
    {
        return $this->objects->get;
    }

}
