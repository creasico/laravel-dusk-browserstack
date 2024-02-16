<?php

namespace Creasi\DuskBrowserStack;

use Creasi\DuskBrowserStack\Process\BrowserStackLocalProcess;
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
     * @var string|null The path to the custom BrowserStackLocal binary.
     */
    protected static $bslocalBinary;

    /**
     * @var \Symfony\Component\Process\Process The BrowserStackLocal process instance.
     */
    protected static $bslocalProcess;

    /**
     * Determine if the BrowserStack Key and User is set.
     */
    private static function hasBrowserStackKey(): bool
    {
        return isset($_SERVER['BROWSERSTACK_ACCESS_KEY']) || isset($_ENV['BROWSERSTACK_ACCESS_KEY']);
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
            'buildName' => self::getBuildName(),
            'projectName' => self::getProjectName(),
            'sessionName' => self::getSessionName(),
            'seleniumVersion' => '4.0.0',
        ]);

        if (static::$bslocalProcess->isRunning() && $localId = self::getLocalIdentifier()) {
            $caps
                ->setCapability('browserstack.local', true)
                ->setCapability('browserstack.localIdentifier', $localId);
        }

        return $caps;
    }

    /**
     * Get session name
     */
    private static function getSessionName(): string
    {
        return str(static::class)->classBasename()->replace('Test', '')->headline();
    }

    private static function getCommitSha(): string
    {
        if ($githubSha = \env('GITHUB_SHA')) {
            return \substr($githubSha, 0, 7);
        }

        return \exec('echo "$(git rev-parse --short HEAD)"');
    }

    private static function getBranchName(): string
    {
        $githubRef = \env('GITHUB_REF');

        if (! $githubRef) {
            return \exec('echo "$(git branch --show-current)"');
        }

        $branchOrPullRequest = \explode('/', $githubRef)[2];

        if (\env('GITHUB_EVENT_NAME') === 'pull_request') {
            return \sprintf('PR #%s', $branchOrPullRequest);
        }

        return $branchOrPullRequest;
    }

    /**
     * Get build name.
     */
    private static function getBuildName(): string
    {
        $build = env('BROWSERSTACK_BUILD_NAME');

        if ($build && (\strlen($build) > 0 && \strlen($build) <= 255)) {
            return $build;
        }

        $sha = self::getCommitSha();
        $branch = self::getBranchName();

        return \sprintf('[%s] %s', $sha, $branch);
    }

    /**
     * Get project name.
     */
    private static function getProjectName(): string
    {
        if ($project = \env('BROWSERSTACK_PROJECT_NAME', \env('GITHUB_REPOSITORY'))) {
            if (\str_contains($project, '/')) {
                $project = \explode('/', $project)[1];
            }

            return $project;
        }

        return \substr(\explode('/', \exec('git remote get-url origin'))[1], 0, -4);
    }

    /**
     * Get local identifier.
     */
    private static function getLocalIdentifier(): string
    {
        $localIdentifier = env('BROWSERSTACK_LOCAL_IDENTIFIER', self::getProjectName().'_'.self::getBuildName());

        return (string) \str($localIdentifier)->replace('/', '_')->slug();
    }

    private static function getDriverURL(): string
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

        $browsers = collect(static::$browsers ?? []);
        $command = \compact('action', 'arguments');

        $browsers->each(
            fn (Browser $browser) => $browser->driver->executeScript('browserstack_executor: '.\json_encode($command))
        );
    }

    /**
     * Start the BrowserStackLocal process.
     *
     * @throws \RuntimeException
     */
    public static function startBrowserStackLocal(array $arguments = []): void
    {
        $arguments = \array_merge($arguments, [
            'key' => env('BROWSERSTACK_ACCESS_KEY'),
            'local-identifier' => self::getLocalIdentifier(),
        ]);

        static::$bslocalProcess = (new BrowserStackLocalProcess(static::$bslocalBinary))
            ->toProcess($arguments);

        static::$bslocalProcess->start();

        static::$bslocalProcess->waitUntil(function ($_, $output): bool {
            if (\str_contains($output, '[ERROR]')) {
                static::$bslocalProcess->stop();
                throw new \RuntimeException(\explode('[ERROR] ', $output)[1]);
            }

            return \str_contains($output, '[SUCCESS]');
        });

        static::afterClass(function () {
            if (static::$bslocalProcess) {
                static::$bslocalProcess->stop();
            }
        });
    }

    /**
     * Set the path to the custom BrowserStackLocal.
     */
    public static function useBrowserStackLocal(string $path): void
    {
        static::$bslocalBinary = $path;
    }
}
