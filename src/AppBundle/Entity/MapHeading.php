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
 * MapHeading
 *
 * @ORM\Table(name="map_heading")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MapHeadingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MapHeading extends AbstractHeading
{
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\InterestCategory", mappedBy="mapHeading", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $interestCategories;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Work", mappedBy="mapHeading", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $works;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="mapHeadings",cascade={"persist"})
     * @ORM\JoinColumn(name="community_id",nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     */
    private $community;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     * @Expose
     * @SerializedName("is_active")
     */
    private $enabled = false;
    
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->interestCategories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->works = new \Doctrine\Common\Collections\ArrayCollection();
        
    }

    /**
     * Add interestCategory
     *
     * @param \AppBundle\Entity\InterestCategory $interestCategory
     *
     * @return MapHeading
     */
    public function addInterestCategory(\AppBundle\Entity\InterestCategory $interestCategory)
    {
        $this->interestCategories[] = $interestCategory;

        return $this;
    }

    /**
     * Remove interestCategory
     *
     * @param \AppBundle\Entity\InterestCategory $interestCategory
     */
    public function removeInterestCategory(\AppBundle\Entity\InterestCategory $interestCategory)
    {
        $this->interestCategories->removeElement($interestCategory);
    }

    /**
     * Get interestCategories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterestCategories()
    {
        return $this->interestCategories;
    }

    /**
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return MapHeading
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
     * @return MapHeading
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
     * Add work
     *
     * @param \AppBundle\Entity\Work $work
     *
     * @return MapHeading
     */
    public function addWork(\AppBundle\Entity\Work $work)
    {
        $this->works[] = $work;

        return $this;
    }

    /**
     * Remove work
     *
     * @param \AppBundle\Entity\Work $work
     */
    public function removeWork(\AppBundle\Entity\Work $work)
    {
        $this->works->removeElement($work);
    }

    /**
     * Get works
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorks()
    {
        return $this->works;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}
