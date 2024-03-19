<?php

namespace Creasi\DuskBrowserStack;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use PHPUnit\Runner\Version;

/**
 * @mixin \Laravel\Dusk\TestCase
 *
 * @property-read static $browsers \Illuminate\Support\Collection<int, Browser>
 */
trait WithBrowserStack
{
    /**
     * The BrowserStackLocal process instance.
     */
    private static ?LocalProcess $bslocalProcess;

    /**
     * Sent back session status to BrowserStack after each test.
     */
    protected function tearDownWithBrowserStack(): void
    {
        if (\version_compare(Version::id(), '10', '<')) {
            $this->browserStack()->setSessionStatus(
                $this->hasFailed() ? 'failed' : 'passed',
                $this->getStatusMessage()
            );

            return;
        }

        $status = $this->status();

        $this->browserStack()->setSessionStatus(
            $status->isSuccess() ? 'passed' : 'failed',
            $status->message()
        );
    }

    /**
     * Get BrowserStack instance.
     */
    final protected function browserStack(): BrowserStack
    {
        return new BrowserStack(static::$browsers);
    }

    /**
     * Configure the DesiredCapabilities for the BrowserStack session.
     *
     * @link https://chromedriver.chromium.org/capabilities
     */
    protected function withBrowserStackCapabilities(array|DesiredCapabilities $caps): DesiredCapabilities
    {
        $sessionName = str(static::class)->classBasename()->replace('Test', '')->headline();
        $localId = BrowserStack::getLocalIdentifier();

        if (\is_array($caps)) {
            $caps = DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $caps);
        }

        $caps
            ->setCapability('buildName', BrowserStack::getBuildName())
            ->setCapability('projectName', BrowserStack::getProjectName())
            ->setCapability('sessionName', $sessionName)
            ->setCapability('acceptInsecureCerts', true);

        if (static::$bslocalProcess->isRunning() && $localId) {
            $caps
                ->setCapability('browserstack.local', true)
                ->setCapability('browserstack.networkLogs', true)
                ->setCapability('browserstack.localIdentifier', $localId);
        }

        return $caps;
    }

    /**
     * Start the BrowserStackLocal process.
     */
    public static function startBrowserStackLocal(array $arguments = []): void
    {
        if (! BrowserStack::hasAccessKey()) {
            return;
        }

        static::$bslocalProcess = BrowserStack::createLocalProcess($arguments);

        static::$bslocalProcess->start();

        static::afterClass(function () {
            if (static::$bslocalProcess) {
                static::$bslocalProcess->stop();
            }
        });
    }
}
