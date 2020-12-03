<?php

namespace ApiBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UserBundle\Entity\UserLoginHistory;
use Doctrine\ORM\EntityManager;

class JWTResponseListener
{
    protected $container;
    protected $em;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * Add public data to the authentication response
     *
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();



//        if ($user && !$user->isCitizen()) {
//            throw new AccessDeniedException('not citizen');
//        }

        if (!$user instanceof UserInterface) {
            return;
        }
        $event->setData($data);
    }
}
