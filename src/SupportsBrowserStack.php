<?php

namespace Creasi\DuskBrowserStack;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Laravel\Dusk\Browser;
use PHPUnit\Runner\Version;

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
     * @var LocalProcess The BrowserStackLocal process instance.
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
        $this->executeBrowserStackCommand('setSessionStatus', $this->getSessionStatus());
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

    /**
     * Backward compatibility for PHPUnit 9.
     */
    private function getSessionStatus(): array
    {
        if (\version_compare(Version::id(), '10.0.0', '<')) {
            return [
                'status' => $this->hasFailed() ? 'failed' : 'passed',
                'reason' => $this->getStatusMessage(),
            ];
        }

        $status = $this->status();

        return [
            'status' => $status->isSuccess() ? 'passed' : 'failed',
            'reason' => $status->message(),
        ];
    }

    /**
     * Get commit sha in short format.
     *
     * @link https://docs.github.com/en/actions/learn-github-actions/variables#default-environment-variables
     */
    private static function getCommitSha(): string
    {
        if ($githubSha = \env('GITHUB_SHA')) {
            return \substr($githubSha, 0, 7);
        }

        return \exec('echo "$(git rev-parse --short HEAD)"');
    }

    /**
     * Get branch name, but if it's a pull request, use the PR number.
     *
     * @link https://docs.github.com/en/actions/learn-github-actions/variables#default-environment-variables
     */
    private static function getBranchName(): string
    {
        static $result;

        if ($result) {
            return $result;
        }

        $githubRef = \env('GITHUB_REF');

        if (! $githubRef) {
            return $result = \exec('echo "$(git branch --show-current)"');
        }

        $branchOrPullRequest = \explode('/', $githubRef)[2];

        if (\env('GITHUB_EVENT_NAME') === 'pull_request') {
            return $result = \sprintf('PR #%s', $branchOrPullRequest);
        }

        return $result = $branchOrPullRequest;
    }

    /**
     * Get build name.
     */
    private static function getBuildName(): string
    {
        static $result;

        if ($result) {
            return $result;
        }

        $build = env('BROWSERSTACK_BUILD_NAME');

        if ($build && (\strlen($build) > 0 && \strlen($build) <= 255)) {
            return $result = $build;
        }

        $sha = self::getCommitSha();
        $branch = self::getBranchName();

        return $result = \sprintf('[%s] %s', $sha, $branch);
    }

    /**
     * Get project name.
     */
    private static function getProjectName(): string
    {
        static $result;

        if ($result) {
            return $result;
        }

        if ($project = \env('BROWSERSTACK_PROJECT_NAME', \env('GITHUB_REPOSITORY'))) {
            if (\str_contains($project, '/')) {
                $project = \explode('/', $project)[1];
            }

            return $result = $project;
        }

        return $result = \substr(\explode('/', \exec('git remote get-url origin'))[1], 0, -4);
    }

    /**
     * Get local identifier.
     */
    private static function getLocalIdentifier(): string
    {
        static $result;

        if ($result) {
            return $result;
        }

        $localIdentifier = env('BROWSERSTACK_LOCAL_IDENTIFIER', self::getProjectName().'_'.self::getBuildName());

        return $result = (string) \str($localIdentifier)->replace('/', '_')->slug();
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
        static::$bslocalProcess = new LocalProcess(static::$bslocalBinary, \array_merge($arguments, [
            'key' => env('BROWSERSTACK_ACCESS_KEY'),
            'local-identifier' => self::getLocalIdentifier(),
            'force-local',
            'force',
        ]));

        static::$bslocalProcess->start();

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
