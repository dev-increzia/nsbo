<?php

namespace UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserBundle extends Bundle
{
    public function boot()
    {
        parent::boot();
        date_default_timezone_set("Europe/Paris");
    }
    
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
