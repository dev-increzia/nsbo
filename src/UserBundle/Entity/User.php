<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @UniqueEntity("username")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('plainPassword', new Assert\Length(array(
            'min' => 6,
            'max' => 50,
            'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
            'maxMessage' => 'Votre mot de passe ne peut pas être plus long que {{ limit }} caractères',
        )));
    }

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @SerializedName("id")
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="create_at", type="date", nullable=false)
     */
    private $createAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     */
    private $createBy;

    /**
     * @var string
     *
     * @ORM\Column(name="update_at", type="date", nullable=false)
     */
    private $updateAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $updateBy;

    /**
     * @var date
     *
     * @ORM\Column(name="birth_date", type="integer", length=255, nullable=true)
     * @Expose
     * @SerializedName("birth_date")
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("lastname")
     * @Type("string")
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("firstname")
     * @Type("string")
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Expose
     * @SerializedName("phone")
     * @Type("string")
     */
    private $phone;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="admins")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $communityAdmin;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Community", inversedBy="suAdmin")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $communitySuAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Abus", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $abuses;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\DeviceToken", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $deviceTokens;
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Article", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comment", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reporting", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $reportings;

    /**
     * @var string
     *
     * @ORM\Column(name="civility", type="string", length=255, columnDefinition="enum('male', 'female')", nullable=true)
     */
    private $civility = 'male';
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Association", cascade={"persist"}, mappedBy="admins")
     */
    private $associationsAdmin;



    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ArticleHeading", cascade={"persist"}, mappedBy="admins")
     */
    private $articleHeadings;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Association", cascade={"persist"}, mappedBy="suAdmin")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $associationsSuAdmin;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City", cascade={"persist"}, inversedBy="users")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Expose
     */
    private $city;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, mappedBy="communityAdmins")
     */
    private $adminCommunities;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Community", cascade={"persist"}, mappedBy="communitySuadmins")
     */
    private $suAdminCommunities;

    
    /**
    * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Merchant", cascade={"persist"}, mappedBy="admins")
    */
    private $merchantsAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Merchant", cascade={"persist"}, mappedBy="suAdmin")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $merchantsSuAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CommunityUsers", cascade={"persist", "remove"}, mappedBy="user")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @SerializedName("community")
     */
    private $communities;
    
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AssociationUser", cascade={"persist", "remove"}, mappedBy="user")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @SerializedName("associations")
     */
    private $associations;
    
    
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MerchantUser", cascade={"persist", "remove"}, mappedBy="user")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @SerializedName("merchant")
     */
    private $merchant;

    

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AccessAdminCommunity", mappedBy="accessUsers", cascade={"persist", "remove"})
     *
     *
     */
    private $access;
    
    
    

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\EventVolunteer", mappedBy="user", cascade={"persist"})
     */
    private $eventVolunteer;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Event", mappedBy="participants", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $eventsParticipant;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\GoodPlan", mappedBy="participants", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $goodPlanParticipants;



   
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArticleLikes", mappedBy="user", cascade={"persist"})
     */
    private $articleLikes;


    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Category", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(name="user_interests")
     *
     * @SerializedName("interests")
     */
    private $interests;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @ORM\OrderBy({"createAt" = "DESC"})
     * @Assert\Valid()
     */
    private $notifications;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Comment", cascade={"persist"}, inversedBy="usersRead")
     * @ORM\JoinTable(name="user_comment_read")
     */
    private $commentsRead;
    
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PushLog", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid()
     */
    private $pushLogs;
    

    /**
     * @var string
     * @Expose
     * @SerializedName("image_url")
     * @Type("string")
     */
    private $imageURL;

    /**
     * @var string
     * @Expose
     * @SerializedName("role")
     * @Type("string")
     */
    private $role;

    /**
     * @var string
     * @Expose
     * @SerializedName("ga")
     * @Type("string")
     */
    private $ga;
    /**
     * @var string
     * @Expose
     * @SerializedName("countUnread")
     * @Type("string")
     */
    private $countUnread;

    /**
     * @var string
     *
     * @SerializedName("citiesIds")
     */
    private $citiesIds;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SurveyResponse", mappedBy="response", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     */
    private $responses;

    private $apiVersion;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->access = new \Doctrine\Common\Collections\ArrayCollection();
        $this->communities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adminCommunities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->suAdminCommunities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->articleHeadings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getCountUnread()
    {
        return $this->countUnread;
    }

    public function setCountUnread($countUnread)
    {
        $this->countUnread = $countUnread;
        return $this;
    }

    public function getImageURL()
    {
        return $this->imageURL;
    }

    public function setImageURL($imageURL)
    {
        $this->imageURL = $imageURL;
        return $this;
    }

    public function getGa()
    {
        return $this->ga;
    }

    public function setGa($ga)
    {
        $this->ga = $ga;
        return $this;
    }

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
    
    public function isReadComment($commentId)
    {
        $return = false;
        foreach ($this->getCommentsRead() as $c) {
            if ($c->getId() == $commentId) {
                $return = true;
                break;
            }
        }
        return $return;
    }
    
    
    public function hasRight($right,$community)
    {
        if($this->isCommunityAdmin($community)){

                foreach ($this->getAccess() as $access) {
                    if ($access->getAccess()->getSlug() == $right &&  $access->getCommunity() === $community) {
                        return true;
                    }
                }


        }

        return false;
    }

    public function getRoleText()
    {
        foreach ($this->getRoles() as $role) {
            switch ($role) {
                case 'ROLE_SUPER_ADMIN':
                    return 'Super Administrateur';
                case 'ROLE_ADMIN':
                    return 'Administrateur';
                case 'ROLE_CITIZEN':
                    return 'Citoyen';
                case 'ROLE_COMMUNITY':
                    return 'Admin communauté';
                case 'ROLE_USER':
                    return 'Utilisateur';
            }
        }
        return 'Utilisateur';
    }

    public function isSuAdmin()
    {
        $isAdmin = false;
        foreach ($this->getRoles() as $role) {
            switch ($role) {
                case 'ROLE_SUPER_ADMIN':
                    $isAdmin = true;
            }
        }
        return $isAdmin;
    }
    
    

    public function isAdmin()
    {
        $isAdmin = false;
        foreach ($this->getRoles() as $role) {
            switch ($role) {
                case 'ROLE_SUPER_ADMIN':
                    $isAdmin = true;
                    // no break
                case 'ROLE_ADMIN':
                    $isAdmin = true;
            }
        }
        return $isAdmin;
    }

    public function isCitizen()
    {
        $is = false;
        foreach ($this->getRoles() as $role) {
            switch ($role) {
                case 'ROLE_CITIZEN':
                    $is = true;
            }
        }
        return $is;
    }

    public function isCommunity()
    {
        $is = false;
        foreach ($this->getRoles() as $role) {
            switch ($role) {
                case 'ROLE_COMMUNITY':
                    $is = true;
            }
        }
        return $is;
    }

    

    public function getCivilityText()
    {
        if ($this->getCivility() == 'female') {
            return 'Mme';
        }

        return 'Mr';
    }

    public function isSuAdminCommunity()
    {
        return $this->communitySuAdmin ? true : false;
    }
    
    public function isAdminCommunity()
    {
        return $this->communityAdmin ? true : false;
    }

    public function isCommunitySuAdmin($community)
    {
        return $this->suAdminCommunities->contains($community);
    }

    public function isCommunityAdmin($community)
    {
        return $this->adminCommunities->contains($community);
    }

    public function getEnabled()
    {
        return $this->enabled;
    }
    public function getAge()
    {
        if (!is_int($this->getBirthDate())) {
            return false;
        }
        $now = new \DateTime('now');
        $dateBirth = new \DateTime($this->getBirthDate() . '-01-01');
        if (!$dateBirth) {
            return false;
        }
        $diff = $now->diff($dateBirth);
        return $diff->y;
    }
    
    public function getUserCitiesIds()
    {
        $cities = array();
        $cities[] = $this->getCommunity()->getCity()->getId();
        foreach ($this->getSecondaryCities() as $value) {
            $cities[] = $value->getId();
        }
        $result = array_unique($cities);
        return $result;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return User
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
     * @return User
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
     * Set birthDate
     *
     * @param string $birthDate
     *
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set civility
     *
     * @param string $civility
     *
     * @return User
     */
    public function setCivility($civility)
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * Get civility
     *
     * @return string
     */
    public function getCivility()
    {
        return $this->civility;
    }



    /**
     * Set createBy
     *
     * @param \UserBundle\Entity\User $createBy
     *
     * @return User
     */
    public function setCreateBy(\UserBundle\Entity\User $createBy = null)
    {
        $this->createBy = $createBy;

        return $this;
    }

    /**
     * Get createBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getCreateBy()
    {
        return $this->createBy;
    }

    /**
     * Set updateBy
     *
     * @param \UserBundle\Entity\User $updateBy
     *
     * @return User
     */
    public function setUpdateBy(\UserBundle\Entity\User $updateBy = null)
    {
        $this->updateBy = $updateBy;

        return $this;
    }

    /**
     * Get updateBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getUpdateBy()
    {
        return $this->updateBy;
    }


    /**
     * Add abus
     *
     * @param \AppBundle\Entity\Abus $abus
     *
     * @return User
     */
    public function addAbus(\AppBundle\Entity\Abus $abus)
    {
        $this->abuses[] = $abus;

        return $this;
    }

    /**
     * Remove abus
     *
     * @param \AppBundle\Entity\Abus $abus
     */
    public function removeAbus(\AppBundle\Entity\Abus $abus)
    {
        $this->abuses->removeElement($abus);
    }

    /**
     * Get abuses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAbuses()
    {
        return $this->abuses;
    }

    /**
     * Add article
     *
     * @param \AppBundle\Entity\Article $article
     *
     * @return User
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
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return User
     */
    public function addComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comment $comment
     */
    public function removeComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add reporting
     *
     * @param \AppBundle\Entity\Reporting $reporting
     *
     * @return User
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

    /**
     * Add associationsAdmin
     *
     * @param \AppBundle\Entity\Association $associationsAdmin
     *
     * @return User
     */
    public function addAssociationsAdmin(\AppBundle\Entity\Association $associationsAdmin)
    {
        $this->associationsAdmin[] = $associationsAdmin;

        return $this;
    }

    /**
     * Remove associationsAdmin
     *
     * @param \AppBundle\Entity\Association $associationsAdmin
     */
    public function removeAssociationsAdmin(\AppBundle\Entity\Association $associationsAdmin)
    {
        $this->associationsAdmin->removeElement($associationsAdmin);
    }

    /**
     * Get associationsAdmin
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociationsAdmin()
    {
        return $this->associationsAdmin;
    }

    

    /**
     * Add merchantsAdmin
     *
     * @param \AppBundle\Entity\Merchant $merchantsAdmin
     *
     * @return User
     */
    public function addMerchantsAdmin(\AppBundle\Entity\Merchant $merchantsAdmin)
    {
        $this->merchantsAdmin[] = $merchantsAdmin;

        return $this;
    }

    /**
     * Remove merchantsAdmin
     *
     * @param \AppBundle\Entity\Merchant $merchantsAdmin
     */
    public function removeMerchantsAdmin(\AppBundle\Entity\Merchant $merchantsAdmin)
    {
        $this->merchantsAdmin->removeElement($merchantsAdmin);
    }

    /**
     * Get merchantsAdmin
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMerchantsAdmin()
    {
        return $this->merchantsAdmin;
    }

    
    /**
     * Add access
     *
     * @param \AppBundle\Entity\AccessAdminCommunity $access
     *
     * @return User
     */
    public function addAccess(\AppBundle\Entity\AccessAdminCommunity $access)
    {
        $this->access[] = $access;

        return $this;
    }

    /**
     * Remove secondaryCity
     *
     * @param \AppBundle\Entity\AccessAdminCommunity $access
     */
    public function removeAccess(\AppBundle\Entity\AccessAdminCommunity $access)
    {
        $this->access->removeElement($access);
    }

    /**
     * Get access
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccess()
    {
        return $this->access;
    }


    /**
     * Set image
     *
     * @param \AppBundle\Entity\File $image
     *
     * @return User
     */
    public function setImage(\AppBundle\Entity\File $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \AppBundle\Entity\File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add eventVolunteer
     *
     * @param \AppBundle\Entity\EventVolunteer $eventVolunteer
     *
     * @return User
     */
    public function addEventVolunteer(\AppBundle\Entity\EventVolunteer $eventVolunteer)
    {
        $this->eventVolunteer[] = $eventVolunteer;

        return $this;
    }

    /**
     * Remove eventVolunteer
     *
     * @param \AppBundle\Entity\EventVolunteer $eventVolunteer
     */
    public function removeEventVolunteer(\AppBundle\Entity\EventVolunteer $eventVolunteer)
    {
        $this->eventVolunteer->removeElement($eventVolunteer);
    }

    /**
     * Get eventVolunteer
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventVolunteer()
    {
        return $this->eventVolunteer;
    }

    /**
     * Add eventsParticipant
     *
     * @param \AppBundle\Entity\Event $eventsParticipant
     *
     * @return User
     */
    public function addEventsParticipant(\AppBundle\Entity\Event $eventsParticipant)
    {
        $this->eventsParticipant[] = $eventsParticipant;

        return $this;
    }

    /**
     * Remove eventsParticipant
     *
     * @param \AppBundle\Entity\Event $eventsParticipant
     */
    public function removeEventsParticipant(\AppBundle\Entity\Event $eventsParticipant)
    {
        $this->eventsParticipant->removeElement($eventsParticipant);
    }

    /**
     * Get eventsParticipant
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventsParticipant()
    {
        return $this->eventsParticipant;
    }

    /**
     * Add interest
     *
     * @param \AppBundle\Entity\Category $interest
     *
     * @return User
     */
    public function addInterest(\AppBundle\Entity\Category $interest)
    {
        $this->interests[] = $interest;

        return $this;
    }

    /**
     * Remove interest
     *
     * @param \AppBundle\Entity\Category $interest
     */
    public function removeInterest(\AppBundle\Entity\Category $interest)
    {
        $this->interests->removeElement($interest);
    }

    /**
     * Get interests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return User
     */
    public function addNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \AppBundle\Entity\Notification $notification
     */
    public function removeNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add commentsRead
     *
     * @param \AppBundle\Entity\Comment $commentsRead
     *
     * @return User
     */
    public function addCommentsRead(\AppBundle\Entity\Comment $commentsRead)
    {
        $this->commentsRead[] = $commentsRead;

        return $this;
    }

    /**
     * Remove commentsRead
     *
     * @param \AppBundle\Entity\Comment $commentsRead
     */
    public function removeCommentsRead(\AppBundle\Entity\Comment $commentsRead)
    {
        $this->commentsRead->removeElement($commentsRead);
    }

    /**
     * Get commentsRead
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentsRead()
    {
        return $this->commentsRead;
    }

    /**
     * Add pushLog
     *
     * @param \AppBundle\Entity\PushLog $pushLog
     *
     * @return User
     */
    public function addPushLog(\AppBundle\Entity\PushLog $pushLog)
    {
        $this->pushLogs[] = $pushLog;

        return $this;
    }

    /**
     * Remove pushLog
     *
     * @param \AppBundle\Entity\PushLog $pushLog
     */
    public function removePushLog(\AppBundle\Entity\PushLog $pushLog)
    {
        $this->pushLogs->removeElement($pushLog);
    }

    /**
     * Get pushLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPushLogs()
    {
        return $this->pushLogs;
    }
    
    public function getApiVersion()
    {
        return (empty($this->apiVersion)) ? "1" : $this->apiVersion;
    }
    
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        
        return $this;
    }


    /**
     * Add deviceToken
     *
     * @param \AppBundle\Entity\DeviceToken $deviceToken
     *
     * @return User
     */
    public function addDeviceToken(\AppBundle\Entity\DeviceToken $deviceToken)
    {
        $this->deviceTokens[] = $deviceToken;
        $deviceToken->setUser($this);
        return $this;
    }

    /**
     * Remove deviceToken
     *
     * @param \AppBundle\Entity\DeviceToken $deviceToken
     */
    public function removeDeviceToken(\AppBundle\Entity\DeviceToken $deviceToken)
    {
        $this->deviceTokens->removeElement($deviceToken);
    }

    /**
     * Get deviceTokens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDeviceTokens()
    {
        return $this->deviceTokens;
    }

    /**
     * Add articleLike
     *
     * @param \AppBundle\Entity\ArticleLikes $articleLike
     *
     * @return User
     */
    public function addArticleLike(\AppBundle\Entity\ArticleLikes $articleLike)
    {
        $this->articleLikes[] = $articleLike;

        return $this;
    }

    /**
     * Remove articleLike
     *
     * @param \AppBundle\Entity\ArticleLikes $articleLike
     */
    public function removeArticleLike(\AppBundle\Entity\ArticleLikes $articleLike)
    {
        $this->articleLikes->removeElement($articleLike);
    }

    /**
     * Get articleLikes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticleLikes()
    {
        return $this->articleLikes;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCommunityAdmin()
    {
        return $this->communityAdmin;
    }

    public function getCommunitySuAdmin()
    {
        return $this->communitySuAdmin;
    }

    
    public function setCommunityAdmin($communityAdmin)
    {
        $this->communityAdmin = $communityAdmin;
    }

    public function setCommunitySuAdmin($communitySuAdmin)
    {
        $this->communitySuAdmin = $communitySuAdmin;
    }



    
    

    /**
     * Add association
     *
     * @param \AppBundle\Entity\AssociationUsers $association
     *
     * @return User
     */
    public function addAssociation(\AppBundle\Entity\AssociationUser $association)
    {
        $this->associations[] = $association;

        return $this;
    }

    /**
     * Remove association
     *
     * @param \AppBundle\Entity\AssociationUsers $association
     */
    public function removeAssociation(\AppBundle\Entity\AssociationUser $association)
    {
        $this->associations->removeElement($association);
    }

    /**
     * Get association
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociation()
    {
        return $this->associations;
    }

    /**
     * Add merchant
     *
     * @param \AppBundle\Entity\MerchantUser $merchant
     *
     * @return User
     */
    public function addMerchant(\AppBundle\Entity\MerchantUser $merchant)
    {
        $this->merchant[] = $merchant;

        return $this;
    }

    /**
     * Remove merchant
     *
     * @param \AppBundle\Entity\MerchantUser $merchant
     */
    public function removeMerchant(\AppBundle\Entity\MerchantUser $merchant)
    {
        $this->merchant->removeElement($merchant);
    }

    /**
     * Get merchant
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMerchant()
    {
        return $this->merchant;
    }

    /**
     * Get associationsSuAdmins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociationsSuAdmins()
    {
        return $this->associationsSuAdmins;
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
     * Add community
     *
     * @param \AppBundle\Entity\CommunityUsers $community
     *
     * @return User
     */
    public function addCommunity(\AppBundle\Entity\CommunityUsers $community)
    {
        $this->communities[] = $community;

        return $this;
    }

    /**
     * Remove community
     *
     * @param \AppBundle\Entity\CommunityUsers $community
     */
    public function removeCommunity(\AppBundle\Entity\CommunityUsers $community)
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

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return User
     */
    public function setCity(\AppBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    

    /**
     * Add associationsSuAdmin
     *
     * @param \AppBundle\Entity\Association $associationsSuAdmin
     *
     * @return User
     */
    public function addAssociationsSuAdmin(\AppBundle\Entity\Association $associationsSuAdmin)
    {
        $this->associationsSuAdmin[] = $associationsSuAdmin;

        return $this;
    }

    /**
     * Remove associationsSuAdmin
     *
     * @param \AppBundle\Entity\Association $associationsSuAdmin
     */
    public function removeAssociationsSuAdmin(\AppBundle\Entity\Association $associationsSuAdmin)
    {
        $this->associationsSuAdmin->removeElement($associationsSuAdmin);
    }

    /**
     * Get associationsSuAdmin
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociationsSuAdmin()
    {
        return $this->associationsSuAdmin;
    }

    /**
     * Add merchantsSuAdmin
     *
     * @param \AppBundle\Entity\Merchant $merchantsSuAdmin
     *
     * @return User
     */
    public function addMerchantsSuAdmin(\AppBundle\Entity\Merchant $merchantsSuAdmin)
    {
        $this->merchantsSuAdmin[] = $merchantsSuAdmin;

        return $this;
    }

    /**
     * Remove merchantsSuAdmin
     *
     * @param \AppBundle\Entity\Merchant $merchantsSuAdmin
     */
    public function removeMerchantsSuAdmin(\AppBundle\Entity\Merchant $merchantsSuAdmin)
    {
        $this->merchantsSuAdmin->removeElement($merchantsSuAdmin);
    }

    /**
     * Get merchantsSuAdmin
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMerchantsSuAdmin()
    {
        return $this->merchantsSuAdmin;
    }

    /**
     * Add goodPlanParticipant
     *
     * @param \AppBundle\Entity\GoodPlan $goodPlanParticipant
     *
     * @return User
     */
    public function addGoodPlanParticipant(\AppBundle\Entity\GoodPlan $goodPlanParticipant)
    {
        $this->goodPlanParticipants[] = $goodPlanParticipant;

        return $this;
    }

    /**
     * Remove goodPlanParticipant
     *
     * @param \AppBundle\Entity\GoodPlan $goodPlanParticipant
     */
    public function removeGoodPlanParticipant(\AppBundle\Entity\GoodPlan $goodPlanParticipant)
    {
        $this->goodPlanParticipants->removeElement($goodPlanParticipant);
    }

    /**
     * Get goodPlanParticipants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGoodPlanParticipants()
    {
        return $this->goodPlanParticipants;
    }

    /**
     * Add response
     *
     * @param \AppBundle\Entity\SurveyResponse $response
     *
     * @return User
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

    /**
     * Add adminCommunity
     *
     * @param \AppBundle\Entity\Community $adminCommunity
     *
     * @return User
     */
    public function addAdminCommunity(\AppBundle\Entity\Community $adminCommunity)
    {
        $this->adminCommunities[] = $adminCommunity;

        return $this;
    }

    /**
     * Remove adminCommunity
     *
     * @param \AppBundle\Entity\Community $adminCommunity
     */
    public function removeAdminCommunity(\AppBundle\Entity\Community $adminCommunity)
    {
        $this->adminCommunities->removeElement($adminCommunity);
    }

    /**
     * Get adminCommunities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdminCommunities()
    {
        return $this->adminCommunities;
    }

    /**
     * Add suAdminCommunity
     *
     * @param \AppBundle\Entity\Community $suAdminCommunity
     *
     * @return User
     */
    public function addSuAdminCommunity(\AppBundle\Entity\Community $suAdminCommunity)
    {
        $this->suAdminCommunities[] = $suAdminCommunity;

        return $this;
    }

    /**
     * Remove suAdminCommunity
     *
     * @param \AppBundle\Entity\Community $suAdminCommunity
     */
    public function removeSuAdminCommunity(\AppBundle\Entity\Community $suAdminCommunity)
    {
        $this->suAdminCommunities->removeElement($suAdminCommunity);
    }

    /**
     * Get suAdminCommunities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSuAdminCommunities()
    {
        return $this->suAdminCommunities;
    }

    /**
     * Add articleHeading
     *
     * @param \AppBundle\Entity\ArticleHeading $articleHeading
     *
     * @return User
     */
    public function addArticleHeading(\AppBundle\Entity\ArticleHeading $articleHeading)
    {
        $this->articleHeadings[] = $articleHeading;

        return $this;
    }

    /**
     * Remove articleHeading
     *
     * @param \AppBundle\Entity\ArticleHeading $articleHeading
     */
    public function removeArticleHeading(\AppBundle\Entity\ArticleHeading $articleHeading)
    {
        $this->articleHeadings->removeElement($articleHeading);
    }

    /**
     * Get articleHeadings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticleHeadings()
    {
        return $this->articleHeadings;
    }
}
