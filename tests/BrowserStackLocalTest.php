<?php

namespace Creasi\Tests;

use Creasi\DuskBrowserStack\BrowserStack;
use Creasi\DuskBrowserStack\WithBrowserStack;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Orchestra\Testbench\Attributes\RequiresEnv;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\Dusk\Options as DuskOptions;
use Orchestra\Testbench\Dusk\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BrowserStackLocalTest extends TestCase
{
    use WithBrowserStack;
    use WithWorkbench;

    public static function defineWebDriverOptions()
    {
        $_ENV['DUSK_DRIVER_URL'] = BrowserStack::getDriverURL();
        $_ENV['DUSK_HEADLESS_DISABLED'] = true;

        static::startBrowserStackLocal();
    }

    protected function driver(): RemoteWebDriver
    {
        if (DuskOptions::shouldUsesWithoutUI()) {
            DuskOptions::withoutUI();
        } elseif ($this->hasHeadlessDisabled()) {
            DuskOptions::withUI();
        }

        $capabilities = DuskOptions::getChromeOptions()->toCapabilities();

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'],
            $this->withBrowserStackCapabilities($capabilities)
        );
    }

    #[Test]
    #[Group('browserStack')]
    #[RequiresEnv('BROWSERSTACK_ACCESS_KEY')]
    public function should_be_true()
    {
        $this->browse(function (Browser $browser) {
            $page = $browser->visit('/');

            $page->assertSee('NOT FOUND');
        });
    }
}
