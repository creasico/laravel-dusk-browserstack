<?php

namespace Creasi\DuskBrowserStack;

use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;

final class BrowserStack
{
    /**
     * The build name cache.
     */
    private static ?string $buildName = null;

    /**
     * The project name cache.
     */
    private static ?string $projectName = null;

    /**
     * The local identifier cache.
     */
    private static ?string $localIdentifier = null;

    /**
     * The run number cache.
     */
    private static ?int $runNumber = null;

    /**
     * @param  Collection<int, Browser>  $browsers
     */
    public function __construct(
        private Collection $browsers
    ) {
        // .
    }

    /**
     * Execute javascript command on BrowserStack.
     *
     * @link https://www.browserstack.com/docs/automate/selenium/js-executors
     */
    public function executeCommand(string $action, ?array $arguments = null): mixed
    {
        $command = \array_filter([
            'action' => $action,
            'arguments' => $arguments,
        ]);

        return $this->browsers->each(
            fn (Browser $browser) => $browser->driver->executeScript('browserstack_executor: '.\json_encode($command))
        );
    }

    /**
     * Set session status on BrowserStack.
     *
     * @link https://www.browserstack.com/docs/automate/selenium/js-executors
     */
    public function setSessionStatus(string $status, string $reason): void
    {
        $this->executeCommand('setSessionStatus', [
            'status' => $status,
            'reason' => $reason,
        ]);
    }

    /**
     * Retreive session details from BrowserStack.
     *
     * @link https://www.browserstack.com/docs/automate/selenium/js-executors
     */
    public function getSessionDetails()
    {
        return $this->executeCommand('getSessionDetails');
    }

    /**
     * Initiate a BrowserStackLocal process.
     */
    public static function createLocalProcess(array $arguments = []): LocalProcess
    {
        return new LocalProcess(\array_merge($arguments, [
            'key' => self::getAccessKey(),
            'local-identifier' => self::getLocalIdentifier(),
        ]));
    }

    /**
     * Check whether the BrowserStack AccessKey is set.
     */
    public static function hasAccessKey(): bool
    {
        return env('BROWSERSTACK_ACCESS_KEY') !== null;
    }

    /**
     * Get the BrowserStack AccessKey.
     */
    public static function getAccessKey(): ?string
    {
        return env('BROWSERSTACK_ACCESS_KEY');
    }

    /**
     * Get the Local Identifier.
     */
    public static function getLocalIdentifier(): string
    {
        if (self::$localIdentifier) {
            return self::$localIdentifier;
        }

        if ($project = env('BROWSERSTACK_LOCAL_IDENTIFIER')) {
            return $project;
        }

        $run = self::getRunsNumber() ?: null;
        $sha = \trim(\exec('git rev-parse --short HEAD').'-'.$run, '- ');

        return self::$localIdentifier = self::getProjectName().'_'.$sha;
    }

    /**
     * Get the Build Name.
     */
    public static function getBuildName(): string
    {
        if (self::$buildName) {
            return self::$buildName;
        }

        $build = env('BROWSERSTACK_BUILD_NAME');

        if ($build && (\strlen($build) > 0 && \strlen($build) <= 255)) {
            return self::$buildName = $build;
        }

        $numbers = '';
        $branch = env('GITHUB_HEAD_REF', \exec('git branch --show-current'));
        $message = self::getRunsMessage();

        if ($runNumber = self::getRunsNumber()) {
            $numbers .= \sprintf(', Run: %d', $runNumber);
        }

        return self::$buildName = \sprintf('[%s] %s%s', $branch, $message, $numbers);
    }

    /**
     * Get the Project Name.
     */
    public static function getProjectName(): string
    {
        if (self::$projectName) {
            return self::$projectName;
        }

        if ($project = env('BROWSERSTACK_PROJECT_NAME')) {
            return $project;
        }

        if ($project = \env('GITHUB_REPOSITORY')) {
            return \explode('/', $project)[1];
        }

        return self::$projectName = \substr(\explode('/', \exec('git remote get-url origin'))[1], 0, -4);
    }

    /**
     * Get the Driver URL.
     */
    public static function getDriverURL(): string
    {
        if (! self::hasAccessKey()) {
            return env('DUSK_DRIVER_URL', 'http://localhost:9515');
        }

        $username = env('BROWSERSTACK_USERNAME');

        return 'https://'.$username.':'.self::getAccessKey().'@hub.browserstack.com/wd/hub';
    }

    /**
     * Check wheter the current branch is dirty.
     */
    private static function isDirty(): bool
    {
        return (bool) \exec('[[ -n `git status --porcelain` ]] && echo 1');
    }

    /**
     * Get local runs number.
     */
    private static function getRunsNumber(): ?int
    {
        $runPath = \dirname(__DIR__).'/.runs';

        // When it runs on github actions, use its run number instead.
        if ($runNumber = \env('GITHUB_RUN_ATTEMPT')) {
            return (int) $runNumber;
        }

        // Check whether the current branch is dirty.
        if (! self::isDirty()) {
            // If there's any runs file, it must be from previous run.
            if (! \file_exists($runPath)) {
                // Get rid of it!
                \unlink($runPath);
            }

            // Don't do anything.
            return null;
        }

        // Please do cache.
        if (self::$runNumber) {
            return self::$runNumber;
        }

        // Create new runs file if it doesn't exist.
        if (! \file_exists($runPath)) {
            \file_put_contents($runPath, '0');
        }

        // Get the run number, which is must be from previous run.
        // Increment the run number and save it back to the file.
        $runNumber = (int) \file_get_contents($runPath);
        \file_put_contents($runPath, self::$runNumber = ++$runNumber);

        return self::$runNumber;
    }

    /**
     * Get run message.
     */
    private static function getRunsMessage(): string
    {
        if (\env('GITHUB_EVENT_NAME') === 'pull_request') {
            return \sprintf('PR #%s', \explode('/', \env('GITHUB_REF'))[2]);
        }

        if (self::isDirty()) {
            return 'uncommited changes';
        }

        return \exec('echo "`git log -1 --pretty=%s` (`git rev-parse --short HEAD`)"');
    }
}
