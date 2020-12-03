<?php

namespace UserBundle\EventListener;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class AuthenticationEventListener implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $tokenStorage;
    protected $authorizationChecker;

    public function __construct(Router $router, TokenStorage $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $cookies = $request->cookies;
        if ($cookies->has('lock')) {
            $response = new RedirectResponse($this->router->generate('app_homepage'));
            $response->headers->clearCookie('lock');
            return $response->send();
        }

        return new RedirectResponse($this->router->generate('app_homepage'));
    }
}
