<?php

namespace Creasi\DuskBrowserStack\Commands;

use Creasi\DuskBrowserStack\LocalBinary;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BrowserStackLocalCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'dusk:browserstack-local
                    {--ssl-no-verify : Bypass SSL certificate verification when installing through a proxy}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Install the Browserstack-Local binary';

    public function handle(): void
    {
        $archive = $this->download();

        $binary = $this->extract($archive);

        $this->rename($binary);
    }

    protected function download(): string
    {
        $client = new Client();
        $resource = Utils::tryFopen($archive = LocalBinary::getDirectory().'/browserstack-local.zip', 'w');

        try {
            $response = $client->get($url = LocalBinary::getDownloadUrl(), \array_merge([
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

        $zip->extractTo(LocalBinary::getDirectory());

        $binary = $zip->getNameIndex(0);

        $zip->close();

        unlink($archive);

        return $binary;
    }

    protected function rename($binary): void
    {
        $os = LocalBinary::getPlatform();
        $binary = str_replace(DIRECTORY_SEPARATOR, '/', $binary);
        $directory = LocalBinary::getDirectory().'/';

        $newName = \str_contains($binary, '/')
            ? Str::after(str_replace('BrowserStackLocal', 'bs-local-'.$os, $binary), '/')
            : str_replace('BrowserStackLocal', 'bs-local-'.$os, $binary);

        rename($directory.$binary, $directory.$newName);

        chmod($directory.$newName, 0755);
    }
}
