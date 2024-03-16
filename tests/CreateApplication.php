<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\DuskBrowserStack\ServiceProvider;

trait CreateApplication
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
