[![Version](https://img.shields.io/packagist/v/creasi/dusk-browserstack?style=flat-square)](https://packagist.org/packages/creasi/dusk-browserstack)
[![License](https://img.shields.io/packagist/l/creasi/dusk-browserstack?style=flat-square)](https://github.com/creasico/laravel-dusk-browserstack/blob/master/LICENSE)

# Additional BrowserStack Local Supports for Laravel Dusk

## Installation

Use [Composer](https://getcomposer.org/)

```bash
$ composer require creasi/dusk-browserstack --dev
```

## Usage

1. Add `WithBrowserStack` to your existing `DuskTestCase`, like so

   ```php
   use Laravel\Dusk\TestCase as BaseTestCase;
   use Creasi\DuskBrowserStack\WithBrowserStack;
 
   abstract class DuskTestCase extends BaseTestCase
   {
       use CreatesApplication;
       use WithBrowserStack;

       // ...
   }
   ```

1. Update `prepare` method

   ```php
   use Creasi\DuskBrowserStack\BrowserStack;

   public static function prepare()
   {
       if (BrowserStack::hasAccessKey()) {
           static::startBrowserStackLocal();
           return;
       }

       if (! static::runningInSail()) {
           static::startChromeDriver();
       }
   }
   ```

1. Update `driver` method

   ```php
   use Creasi\DuskBrowserStack\BrowserStack;

   protected function driver()
   {
       // ...

       $capabilities = DesiredCapabilities::chrome()
           ->setCapability(ChromeOptions::CAPABILITY, $options);
       
       return RemoteWebDriver::create(
           BrowserStack::getDriverURL(),
           $this->withBrowserStackCapabilities($capabilities)
       );
   }
   ```

1. Last one, don't forget to update your `.env` file

   ```sh
   BROWSERSTACK_USERNAME='<your-browserstack-username>'
   BROWSERSTACK_ACCESS_KEY='<your-browserstack-access-key>'
   ```

## License

This library is open-sourced software licensed under [MIT license](LICENSE).
