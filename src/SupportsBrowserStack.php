<?php

namespace Creasi\DuskBrowserStack;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Laravel\Dusk\Browser;

/**
 * @mixin \Laravel\Dusk\TestCase
 * @mixin \PHPUnit\Framework\TestCase
 *
 * @property-read static $browsers Illuminate\Support\Collection<int, Browser>
 */
trait SupportsBrowserStack
{
    /**
     * Determine if the BrowserStack Key and User is set.
     */
    private static function hasBrowserStackKey(): bool
    {
        return (isset($_SERVER['BROWSERSTACK_ACCESS_KEY']) || isset($_ENV['BROWSERSTACK_ACCESS_KEY']))
            && env('BROWSERSTACK_LOCAL_IDENTIFIER') !== null;
    }

    /**
     * Sending assertion result back to BrowserStack.
     */
    protected function tearDownSupportsBrowserStack(): void
    {
        $status = $this->status();

        $this->executeBrowserStackCommand('setSessionStatus', [
            'status' => $status->isSuccess() ? 'passed' : 'failed',
            'reason' => $status->message(),
        ]);
    }

    private function withBrowserStackCapabilities(DesiredCapabilities $caps): DesiredCapabilities
    {
        if (! static::hasBrowserStackKey()) {
            return $caps;
        }

        $caps->setCapability('bstack:options', [
            // 'os' => 'Windows',
            // 'osVersion' => '10',
            'buildName' => $this->getBuildName(),
            'projectName' => $this->getProjectName(),
            'sessionName' => $this->getSessionName(),
            'seleniumVersion' => '4.0.0',
        ]);

        if ($localId = env('BROWSERSTACK_LOCAL_IDENTIFIER')) {
            $caps
                ->setCapability('browserstack.local', true)
                ->setCapability('browserstack.localIdentifier', $localId);
        }

        return $caps;
    }

    /**
     * Get session name
     */
    private function getSessionName(): string
    {
        return str(\get_class($this))->classBasename()->replace('Test', '')->headline();
    }

    /**
     * Get build name
     */
    private function getBuildName(): string
    {
        $build = env('BROWSERSTACK_BUILD_NAME');

        if ($build && (\strlen($build) > 0 && \strlen($build) <= 255)) {
            return $build;
        }

        return \exec('echo "$(git branch --show-current)-$(git rev-parse --short HEAD)"');
    }

    /**
     * Get project name
     */
    private function getProjectName(): string
    {
        if ($project = env('BROWSERSTACK_PROJECT_NAME')) {
            return $project;
        }

        return \substr(\explode('/', \exec('git remote get-url origin'))[1], 0, -4);
    }

    private function getDriverURL(): string
    {
        if (static::hasBrowserStackKey()) {
            return 'https://'.env('BROWSERSTACK_USERNAME').':'.env('BROWSERSTACK_ACCESS_KEY').'@hub.browserstack.com/wd/hub';
        }

        return env('DUSK_DRIVER_URL', 'http://localhost:9515');
    }

    private function executeBrowserStackCommand(string $action, array $arguments): void
    {
        if (! static::hasBrowserStackKey()) {
            return;
        }

        $browsers = static::$browsers ?? collect();
        $command = \compact('action', 'arguments');

        $browsers->each(
            fn (Browser $browser) => $browser->driver->executeScript('browserstack_executor: '.\json_encode($command))
        );
    }
}
