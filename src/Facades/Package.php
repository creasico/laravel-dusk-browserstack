<?php

namespace Creasi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Package extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'creasi.package';
    }
}
