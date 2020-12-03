<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UsefullLinkHeading
 *
 * @ORM\Table(name="usefull_link_heading")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UsefullLinkHeadingRepository")
 */
class UsefullLinkHeading extends AbstractHeading
{


    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="usefullLinkHeadings",cascade={"persist"})
     * @ORM\JoinColumn(name="community_id",nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     */
    private $community;


    /**
     * Set url
     *
     * @param string $url
     *
     * @return UsefullLinkHeading
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return UsefullLinkHeading
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
     * Set community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return UsefullLinkHeading
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
}
