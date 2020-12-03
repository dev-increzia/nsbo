<?php

namespace UserBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernel;

class LockListener
{
    protected $request;
    private $container;

    public function __construct(RequestStack $requestStack, Container $container)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $cookies = $this->request->cookies;
        $routeName = $this->request->get('_route');
        if ($cookies->has('lock')) {
            if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
                return;
            } elseif ($event->getRequest()->getRequestFormat() == 'css' || $event->getRequest()->getRequestFormat() == 'js' || $event->getRequest()->getRequestFormat() == 'jpg') {
                return;
            } else {
                if ($routeName != 'fos_user_resetting_reset' && $routeName != 'fos_user_resetting_check_email' && $routeName != 'fos_user_security_logout' && $routeName != 'fos_user_resetting_send_email' && $routeName != 'fos_user_resetting_request' && $routeName != 'fos_user_security_login' && $routeName != 'fos_user_security_check' && $routeName != 'app_lock' && $routeName != '_wdt') {
                    $url = $this->container->get('router')->generate('app_lock');
                    $event->setController(function () use ($url) {
                        return new RedirectResponse($url);
                    });
                }
            }
        }
    }
}
