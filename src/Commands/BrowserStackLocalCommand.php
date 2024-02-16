<?php

namespace Creasi\DuskBrowserStack\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BrowserStackLocalCommand extends Command
{
    /** @var string */
    protected $signature = 'dusk:browserstack-local
                    {--ssl-no-verify : Bypass SSL certificate verification when installing through a proxy}';

    /** @var string */
    protected $description = 'Install the Browserstack-Local binary';

    /** @var string Path to the bin directory. */
    protected $directory = __DIR__.'/../../bin';

    public function handle(): void
    {
        $os = $this->getPlatform();

        $archive = $this->download($os);

        $binary = $this->extract($archive);

        $this->rename($binary, $os);
    }

    protected function download(string $os): string
    {
        $client = new Client();
        $url = "https://www.browserstack.com/browserstack-local/BrowserStackLocal-{$os}.zip";

        $resource = Utils::tryFopen($archive = \realpath($this->directory).'/browserstack-local.zip', 'w');

        try {
            $response = $client->get($url, \array_merge([
                'sink' => $resource,
                'verify' => $this->option('ssl-no-verify') === false,
            ]));

            if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
                throw new \Exception("Unable to fetch contents from [{$url}].");
            }
        } catch (\Throwable $e) {
            unlink($archive);
            throw $e;
        }

        return $archive;
    }

    protected function extract($archive): string
    {
        $zip = new \ZipArchive;

        $res = $zip->open($archive);

        if ($res !== true) {
            throw new \Exception("Unable to open [{$archive}]. code: {$res}");
        }

        $zip->extractTo($this->directory);

        $binary = $zip->getNameIndex(0);

        $zip->close();

        unlink($archive);

        return $binary;
    }

    protected function rename($binary, $os): void
    {
        $binary = str_replace(DIRECTORY_SEPARATOR, '/', $binary);
        $directory = \realpath($this->directory).'/';

        $newName = \str_contains($binary, '/')
            ? Str::after(str_replace('BrowserStackLocal', 'bs-local-'.$os, $binary), '/')
            : str_replace('BrowserStackLocal', 'bs-local-'.$os, $binary);

        rename($directory.$binary, $directory.$newName);

        chmod($directory.$newName, 0755);
    }

    protected function getPlatform(): string
    {
        $os = \strtoupper(PHP_OS);

        if ($os === 'WINNT' || \str_contains(php_uname(), 'Microsoft')) {
            return 'win32';
        }

        if ($os === 'DARWIN') {
            return 'darwin-x64';
        }

        return 'linux-x64';
    }
}
