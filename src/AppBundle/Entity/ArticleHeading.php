<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ArticleHeading
 *
 * @ORM\Table(name="article_heading")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArticleHeadingRepository")
 */
class ArticleHeading extends AbstractHeading
{
    /**
     * @var string
     *
     * @ORM\Column(name="emailAdmin", type="string", nullable=true)
     */
    private $emailAdmin;

    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User", inversedBy="articleHeadings", cascade={"persist"})
     * @ORM\JoinTable(name="article_heading_admins")
     */
    private $admins;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Article", mappedBy="articleHeading", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $articles;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="articleHeadings",cascade={"persist"})
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
     * Set emailAdmin
     *
     * @param \DateTime $emailAdmin
     *
     * @return ArticleHeading
     */
    public function setEmailAdmin($emailAdmin)
    {
        $this->emailAdmin = $emailAdmin;

        return $this;
    }

    /**
     * Get emailAdmin
     *
     * @return \DateTime
     */
    public function getEmailAdmin()
    {
        return $this->emailAdmin;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->admins = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return ArticleHeading
     */
    public function addArticle(\AppBundle\Entity\Article $article)
    {
        $this->articles[] = $article;

        return $this;
    }

    /**
     * Remove article
     *
     * @param \AppBundle\Entity\Article $article
     */
    public function removeArticle(\AppBundle\Entity\Article $article)
    {
        $this->articles->removeElement($article);
    }

    /**
     * Get articles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return ArticleHeading
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
     * @return ArticleHeading
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
     * Add admin
     *
     * @param \UserBundle\Entity\User $admin
     *
     * @return ArticleHeading
     */
    public function addAdmin(\UserBundle\Entity\User $admin)
    {
        $this->admins[] = $admin;

        return $this;
    }

    /**
     * Remove admin
     *
     * @param \UserBundle\Entity\User $admin
     */
    public function removeAdmin(\UserBundle\Entity\User $admin)
    {
        $this->admins->removeElement($admin);
    }

    /**
     * Get admins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdmins()
    {
        return $this->admins;
    }
}
