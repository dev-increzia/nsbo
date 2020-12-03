<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class AccessDeniedListener
{
    public function onException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException()->getMessage() == "not citizen") {
            $data = [
                "errorCode" => '17',
                'errorMessage' => "Seuls les comptes citoyens sont autorisés à se connecter.",
            ];
            $content = array(
                'code' => 401,
                'message' => $data
            );
            $response = new Response();
            $response->setContent(json_encode($content));
            $response->setStatusCode(401);

            $event->setResponse($response);
        }
    }
}
