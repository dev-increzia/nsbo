<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function boot()
    {
        parent::boot();
        date_default_timezone_set("Europe/Paris");
    }
}
