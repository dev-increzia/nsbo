<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class OnReponseJsonListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $headers = $event->getResponse()->headers;

        $headers->set('Access-Control-Allow-Origin', '*');
    }
}
