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
 * CategoryPhoneBookHeading
 *
 * @ORM\Table(name="category_phone_book_heading")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryPhoneBookHeadingRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class CategoryPhoneBookHeading
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Expose
     */
    private $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PhoneBookHeading", cascade={"persist"}, inversedBy="objects")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $phoneBookHeading;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Number", mappedBy="categoryPhoneBookHeading", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $numbers;
    

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->numbers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return CategoryPhoneBookHeading
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
     * Set phoneBookHeading
     *
     * @param \AppBundle\Entity\PhoneBookHeading $phoneBookHeading
     *
     * @return CategoryPhoneBookHeading
     */
    public function setPhoneBookHeading(\AppBundle\Entity\PhoneBookHeading $phoneBookHeading = null)
    {
        $this->phoneBookHeading = $phoneBookHeading;

        return $this;
    }

    /**
     * Get phoneBookHeading
     *
     * @return \AppBundle\Entity\PhoneBookHeading
     */
    public function getPhoneBookHeading()
    {
        return $this->phoneBookHeading;
    }

    /**
     * Add number
     *
     * @param \AppBundle\Entity\Number $number
     *
     * @return CategoryPhoneBookHeading
     */
    public function addNumber(\AppBundle\Entity\Number $number)
    {
        $this->numbers[] = $number;

        return $this;
    }

    /**
     * Remove number
     *
     * @param \AppBundle\Entity\Number $number
     */
    public function removeNumber(\AppBundle\Entity\Number $number)
    {
        $this->numbers->removeElement($number);
    }

    /**
     * Get numbers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
