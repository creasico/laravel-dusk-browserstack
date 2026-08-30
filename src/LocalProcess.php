<?php

declare(strict_types=1);

namespace Creasi\DuskBrowserStack;

use Symfony\Component\Process\Process;

class LocalProcess
{
    /**
     * Process pointer reference.
     */
    protected Process $process;

    public function __construct(array $arguments = [])
    {
        $binary = LocalBinary::getPath();

        if (! $binary) {
            throw new \RuntimeException('Unable to locate the BrowserStackLocal binary');
        }

        $commands = [$binary];
        foreach (\array_filter($arguments) as $key => $value) {
            if (\is_numeric($key)) {
                $commands[] = '--'.\trim($value, '- ');

                continue;
            }

            if (! empty($value)) {
                $commands[] = \sprintf('--%s=%s', \trim($key, '- '), $value);
            }
        }

        $this->process = new Process($commands);
    }

    /**
     * Start the browserstack-local process.
     */
    public function start(): void
    {
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
        if (! $this->isRunning()) {
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
