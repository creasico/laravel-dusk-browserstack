<?php

namespace Creasi\DuskBrowserStack\Process;

use Creasi\DuskBrowserStack\Commands\BrowserStackLocalCommand;
use Symfony\Component\Process\Process;

class BrowserStackLocalProcess
{
    public function __construct(
        public ?string $binary
    ) {
        // .
    }

    public function toProcess(array $arguments = []): Process
    {
        if (! $this->binary) {
            $os = BrowserStackLocalCommand::getPlatform();

            if ($os === 'win32') {
                $os .= '.exe';
            }

            $this->binary = __DIR__.'/../../bin/bs-local-'.$os;
        }

        $binary = \realpath($this->binary);

        if (! $binary) {
            throw new \RuntimeException("Unable to locate the BrowserStackLocal binary: {$this->binary}");
        }

        $args = [];
        foreach (\array_filter($arguments) as $key => $value) {
            if (\is_numeric($key)) {
                $args[] = $value;

                continue;
            }

            $args[] = \sprintf('--%s=%s', \trim($key, '- '), $value);
        }

        return new Process(\array_merge([$binary], $args));
    }
}
