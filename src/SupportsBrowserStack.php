<?php

namespace Creasi\DuskBrowserStack;

/**
 * @deprecated use `Creasi\DuskBrowserStack\WithBrowserStack` instead.
 */
trait SupportsBrowserStack
{
    use WithBrowserStack;

    /**
     * Check whether the BrowserStack AccessKey is set.
     *
     * @deprecated use `BrowserStack::hasAccessKey()` instead.
     */
    private static function hasBrowserStackKey(): bool
    {
        return BrowserStack::hasAccessKey();
    }

    /**
     * Get the Driver URL.
     *
     * @deprecated use `BrowserStack::getDriverURL()` instead.
     */
    private static function getDriverURL(): string
    {
        return BrowserStack::getDriverURL();
    }
}
