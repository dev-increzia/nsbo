<?php

namespace ApiBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    protected $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        if (!$this->request) {
            return;
        }
        $datas      = $this->request->getContent();
        $data       = (array) json_decode($datas);
        $apiVersion = "2";
        
        if ($this->request->request->has('api')) {
            $apiVersion = $this->request->request->get('api');
        } elseif (isset($data['api'])) {
            $apiVersion = $data['api'];
        } else {
            $pos = strpos($this->request->getRequestUri(), 'v2');
            
            if ($pos === false) { // We are targeting /api/
                $apiVersion = "2"; // Default mode = V2
            } else {
                $apiVersion = "2"; // Default mode = V2 ( yeah, too )
            }
        }
        $payload = $event->getData();

        $payload['apiVersion'] = $apiVersion;

        $event->setData($payload);
    }
}
