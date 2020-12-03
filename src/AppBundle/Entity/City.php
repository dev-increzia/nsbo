<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CityRepository")
 * @UniqueEntity("name")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class City
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @SerializedName("id")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     */
    private $createAt;

    /**
     * @var string
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=false)
     */
    private $updateAt;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Expose
     * @SerializedName("name")
     * @Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("code")
     */
    private $zipcode;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Association", mappedBy="city", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $associations;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Merchant", mappedBy="city", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $merchants;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Community", mappedBy="city")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $communities;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Article", mappedBy="city", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $articles;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", nullable=true)
     * @Expose
     * @SerializedName("longitude")
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", nullable=true)
     * @Expose
     * @SerializedName("latitude")
     */
    private $latitude;

    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\User", mappedBy="city", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $users;

    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event", mappedBy="city", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $events;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

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
        $this->associations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->merchants = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cityUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return City
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
     * @return City
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
     * Set name
     *
     * @param string $name
     *
     * @return City
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     *
     * @return City
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return City
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return City
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Add association
     *
     * @param \AppBundle\Entity\Association $association
     *
     * @return City
     */
    public function addAssociation(\AppBundle\Entity\Association $association)
    {
        $this->associations[] = $association;

        return $this;
    }

    /**
     * Remove association
     *
     * @param \AppBundle\Entity\Association $association
     */
    public function removeAssociation(\AppBundle\Entity\Association $association)
    {
        $this->associations->removeElement($association);
    }

    /**
     * Get associations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Add merchant
     *
     * @param \AppBundle\Entity\Merchant $merchant
     *
     * @return City
     */
    public function addMerchant(\AppBundle\Entity\Merchant $merchant)
    {
        $this->merchants[] = $merchant;

        return $this;
    }

    /**
     * Remove merchant
     *
     * @param \AppBundle\Entity\Merchant $merchant
     */
    public function removeMerchant(\AppBundle\Entity\Merchant $merchant)
    {
        $this->merchants->removeElement($merchant);
    }

    /**
     * Get merchants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMerchants()
    {
        return $this->merchants;
    }

    

    /**
     * Add article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return City
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
     * Add user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return City
     */
    public function addUser(\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \UserBundle\Entity\User $user
     */
    public function removeUser(\UserBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add cityUser
     *
     * @param \UserBundle\Entity\User $cityUser
     *
     * @return City
     */
    public function addCityUser(\UserBundle\Entity\User $cityUser)
    {
        $this->cityUsers[] = $cityUser;

        return $this;
    }

    /**
     * Remove cityUser
     *
     * @param \UserBundle\Entity\User $cityUser
     */
    public function removeCityUser(\UserBundle\Entity\User $cityUser)
    {
        $this->cityUsers->removeElement($cityUser);
    }

    /**
     * Get cityUsers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCityUsers()
    {
        return $this->cityUsers;
    }

    /**
     * Add event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return City
     */
    public function addEvent(\AppBundle\Entity\Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param \AppBundle\Entity\Event $event
     */
    public function removeEvent(\AppBundle\Entity\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return City
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Add community
     *
     * @param \AppBundle\Entity\Community $community
     *
     * @return City
     */
    public function addCommunity(\AppBundle\Entity\Community $community)
    {
        $this->communities[] = $community;

        return $this;
    }

    /**
     * Remove community
     *
     * @param \AppBundle\Entity\Community $community
     */
    public function removeCommunity(\AppBundle\Entity\Community $community)
    {
        $this->communities->removeElement($community);
    }

    /**
     * Get communities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommunities()
    {
        return $this->communities;
    }
}
