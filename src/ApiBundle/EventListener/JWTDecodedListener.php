<?php

namespace ApiBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class JWTDecodedListener
{
    protected $request;
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, RequestStack $requestStack, $container)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->em = $em;
        $this->container = $container;
    }

    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        if (!$this->request) {
            return;
        }
        $payload = $event->getPayload();
        $em = $this->em;
        $user = $em->getRepository('UserBundle:User')->findOneBy(array('username' => $payload['username']));

        if (!$user) {
            $event->markAsInvalid();

            return;
        }

        //$apiVersion = ( isset($payload['apiVersion']) ) ? $payload['apiVersion'] : '2';
        $apiVersion = $this->request->headers->get('Api-Version');
        //  $appVersion = $this->request->headers->get('App-Version');

        $pos = strpos($this->request->getRequestUri(), 'v2');

        if ($pos === false) { // We are targeting /api/

            if (strpos($this->request->getRequestUri(), 'api/user') == false && strpos($this->request->getRequestUri(), 'api/article/citzen/home') == false) {
                if ($apiVersion == "1") {
                    $event->markAsInvalid();
                    $this->container->get('mobile')->pushNotification($user, 'NOUS-ENSEMBLE ', "L'application Nous Ensemble a évolué. Pour une meilleure utilisation merci de faire sa mise à jour.", false, false, false, false, false, 'on');
                }
            }
        } else { // we are targeting
            $apiVersion = '2';
        }

        $user->setApiVersion($apiVersion);

//        if ($user->getCommunity()) {
//            $gaId = $user->getCommunity()->getGaApplication();
//            $user->setGa($gaId);
//        }

        if (/* !isset($payload['ip']) || $payload['ip'] !== $this->request->getClientIp() || */!$user || !$user->isEnabled()/* || !$user->isCitizen() */) {
            $event->markAsInvalid();
        }
    }
}
