<?php

namespace Creasi\DuskBrowserStack;

class LocalBinary
{
    /**
     * @var string Path to the bin directory.
     */
    private static string $directory = __DIR__.'/../bin';

    /**
     * Retrieve the download URL for the BrowserStackLocal binary.
     */
    public static function getDownloadUrl(): string
    {
        $os = static::getPlatform();

        return "https://www.browserstack.com/browserstack-local/BrowserStackLocal-{$os}.zip";
    }

    /**
     * Retrieve the path to the BrowserStackLocal binary.
     */
    public static function getPath(): string
    {
        $os = static::getPlatform();

        if ($os === 'win32') {
            $os .= '.exe';
        }

        return self::$directory.'/bs-local-'.$os;
    }

    /**
     * Retrieve the download directory for the BrowserStackLocal binary.
     */
    public static function getDirectory(): string
    {
        return \realpath(self::$directory);
    }

    /**
     * Retrieve the platform for the BrowserStackLocal binary.
     */
    public static function getPlatform(): string
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
