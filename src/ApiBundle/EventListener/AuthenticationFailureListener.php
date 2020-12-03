<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthenticationFailureListener
{
    protected $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $data = array(
            'errorMessage' => "Une erreur d'authentification est survenue.",
        );
        if ($event->getException()->getMessage() == 'User account is disabled.') {
            $data = [
                'errorCode' => '20',
                'errorMessage' => "Votre compte est désactivé, Veuillez contacter l'administrateur.",
            ];
        } else {
            $message = $event->getException()->getPrevious()->getMessage();
            $methods = get_class_methods($event->getException()->getPrevious());
            $parameters = $this->request->request->all();
            if (!array_key_exists('password', $parameters) || $message == 'The presented password cannot be empty.') {
                $data = [
                    'errorCode' => '12',
                    'errorMessage' => 'Vous devez indiquer votre mot de passe',
                ];
            } elseif (in_array('getUsername', $methods) || !array_key_exists('username', $parameters)) {
                $username = in_array('getUsername', $methods) ? $event->getException()->getPrevious()->getUsername() : '';
                if (($username && $message == 'Username "' . $username . '" does not exist.')) {
                    if ($username == 'NONE_PROVIDED' || !array_key_exists('username', $parameters)) {
                        $data = [
                            'errorCode' => '11',
                            'errorMessage' => 'Vous devez indiquer votre identiﬁant.',
                        ];
                    } else {
                        $data = [
                            'errorCode' => '13',
                            'errorMessage' => 'Votre identiﬁant et votre mot de passe sont incorrects.',
                        ];
                    }
                }
            } else {
                $data = [
                    'errorCode' => '13',
                    'errorMessage' => 'Votre identiﬁant et votre mot de passe sont incorrects.',
                ];
            }
        }
        $response = new JWTAuthenticationFailureResponse($data);
        $event->setResponse($response);
    }
}
