<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NavigationLog
 *
 * @ORM\Table(name="navigation_log")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NavigationLogRepository")
 */
class NavigationLog
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
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="user_firstname", type="string", length=255)
     */
    private $userFirstname;

    /**
     * @var string
     *
     * @ORM\Column(name="user_lastname", type="string", length=255)
     */
    private $userLastname;

    /**
     * @var string
     *
     * @ORM\Column(name="communities", type="text")
     */
    private $communities;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetimetz")
     */
    private $date;

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
     * Set userId
     *
     * @param integer $userId
     *
     * @return NavigationLog
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userFirstname
     *
     * @param string $userFirstname
     *
     * @return NavigationLog
     */
    public function setUserFirstname($userFirstname)
    {
        $this->userFirstname = $userFirstname;

        return $this;
    }

    /**
     * Get userFirstname
     *
     * @return string
     */
    public function getUserFirstname()
    {
        return $this->userFirstname;
    }

    /**
     * Set userLastname
     *
     * @param string $userLastname
     *
     * @return NavigationLog
     */
    public function setUserLastname($userLastname)
    {
        $this->userLastname = $userLastname;

        return $this;
    }

    /**
     * Get userLastname
     *
     * @return string
     */
    public function getUserLastname()
    {
        return $this->userLastname;
    }

    /**
     * Set communities
     *
     * @param string $communities
     *
     * @return NavigationLog
     */
    public function setCommunities($communities)
    {
        $this->communities = $communities;

        return $this;
    }

    /**
     * Get communities
     *
     * @return string
     */
    public function getCommunities()
    {
        return $this->communities;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return NavigationLog
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}

