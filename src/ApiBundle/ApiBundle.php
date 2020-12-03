<?php

namespace ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiBundle extends Bundle
{
    public function boot()
    {
        parent::boot();
        date_default_timezone_set("Europe/Paris");
    }
}
