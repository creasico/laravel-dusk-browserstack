<?php

namespace Creasi\DuskBrowserStack;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Laravel\Dusk\Browser;

class ServiceProvider extends IlluminateServiceProvider
{
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            $this->commands([
                Commands\BrowserStackLocalCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        if (app()->environment('testing') && \class_exists(Browser::class)) {
            $this->registerDuskMacroForInertia();
        }
    }

    /**
     * Register inertia.js helper for dusk testing
     *
     * @see https://github.com/protonemedia/inertiajs-events-laravel-dusk
     */
    private function registerDuskMacroForInertia(): void
    {
        if (! \class_exists(\Inertia\Inertia::class)) {
            return;
        }

        Browser::macro('waitForInertia', function (?int $seconds = null): Browser {
            /** @var Browser $this */
            $driver = $this->driver;

            $currentCount = $driver->executeScript('return window.__inertiaNavigatedCount;');

            return $this->waitUsing($seconds, 100, fn () => $driver->executeScript(
                "return window.__inertiaNavigatedCount > {$currentCount};"
            ), 'Waited %s seconds for Inertia.js to increase the navigate count.');
        });
    }
}
