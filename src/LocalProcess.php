<?php

namespace Creasi\DuskBrowserStack;

use Symfony\Component\Process\Process;

class LocalProcess
{
    /**
     * Command reference.
     */
    protected array $commands = [];

    /**
     * Process pointer reference.
     */
    protected ?Process $process = null;

    public function __construct(array $arguments = [])
    {
        $binary = LocalBinary::getPath();

        if (! \realpath($binary)) {
            throw new \RuntimeException("Unable to locate the BrowserStackLocal binary: {$binary}");
        }

        $this->commands = [$binary];
        foreach (\array_filter($arguments) as $key => $value) {
            if (\is_numeric($key)) {
                $this->commands[] = '--'.\trim($value, '- ');

                continue;
            }

            $this->commands[] = \sprintf('--%s=%s', \trim($key, '- '), $value);
        }
    }

    /**
     * Start the browserstack-local process.
     */
    public function start(): void
    {
        $this->process = new Process($this->commands);

        $this->process->start();

        $this->process->waitUntil(function ($type, $output): bool {
            $message = \explode(' -- ', trim($output))[1];

            if ($type === Process::ERR) {
                throw new \RuntimeException($message);
            }

            return \str_contains($message, '[SUCCESS]');
        });

        // We register the below, so if php is exited early, the child
        // process for the server is closed down, rather than left
        // hanging around for the user to close themselves.
        register_shutdown_function(function () {
            $this->stop();
        });
    }

    /**
     * Stop the browserstack-local process.
     */
    public function stop(): void
    {
        if (! isset($this->process)) {
            return;
        }

        $this->process->stop();
    }

    /**
     * Check whether browserstack-local process is running.
     */
    public function isRunning(): bool
    {
        return $this->process?->isRunning() ?? false;
    }
}
