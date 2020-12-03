<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class Community
{
    protected $tokenStorage;
    protected $container;
    protected $em;

    public function __construct(TokenStorage $tokenStorage, Container $container)
    {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * @param bool $default
     * @return \AppBundle\Entity\Community|null
     * @throws \Exception
     */
    public function getCommunity($default = false)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        //dump($user);

            $session = $this->container->get('session');
            $communityId = $session->get('communityId');
            if ($communityId && $communityId != '') {
                $community = $this->em->getRepository('AppBundle:Community')->find($communityId);
            } else {

                    //load user cityhall
                    if ($user && is_object($user)) {
                        if($user->hasRole('ROLE_COMMUNITY_ADMIN')){
                            $community = $user->getAdminCommunities()[0];
                        }elseif ($user->hasRole('ROLE_COMMUNITY_SU_ADMIN')){
                            $community = $user->getSuAdminCommunities()[0];
                        }

                    }

            }

        if ($default) {
            if (!isset($community) || !$community) {
                $community = $this->em->getRepository('AppBundle:Community')->findOneByEnabled(1);
            }

//            if (!$community)
//                throw new \Exception('What\'s happening ?');
        }



        return isset($community) ? $community : null;
    }
}
