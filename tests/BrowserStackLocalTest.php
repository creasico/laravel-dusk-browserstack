<?php

namespace Creasi\Tests;

use Creasi\DuskBrowserStack\ServiceProvider;
use Creasi\DuskBrowserStack\SupportsBrowserStack;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\Options as DuskOptions;
use Orchestra\Testbench\Dusk\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BrowserStackLocalTest extends TestCase
{
    use SupportsBrowserStack;

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app->useEnvironmentPath(\dirname(__DIR__));
    }

    public static function defineWebDriverOptions()
    {
        $_ENV['DUSK_DRIVER_URL'] = self::getDriverURL();
        $_ENV['DUSK_HEADLESS_DISABLED'] = true;

        if (static::hasBrowserStackKey()) {
            static::startBrowserStackLocal();
        }
    }

    protected function driver(): RemoteWebDriver
    {
        if (DuskOptions::shouldUsesWithoutUI()) {
            DuskOptions::withoutUI();
        } elseif ($this->hasHeadlessDisabled()) {
            DuskOptions::withUI();
        }

        $capabilities = DesiredCapabilities::chrome()
            ->setCapability(ChromeOptions::CAPABILITY, DuskOptions::getChromeOptions());

        return RemoteWebDriver::create(
            self::getDriverURL(),
            $this->withBrowserStackCapabilities($capabilities)
        );
    }

    #[Test]
    #[Group('browserStack')]
    public function should_be_true()
    {
        $this->browse(function (Browser $browser) {
            $page = $browser->visit('/');

            $page->assertSee('NOT FOUND');
        });
    }
}
