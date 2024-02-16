[![Version](https://img.shields.io/packagist/v/creasi/dusk-browserstack?style=flat-square)](https://packagist.org/packages/creasi/dusk-browserstack)
[![License](https://img.shields.io/packagist/l/creasi/dusk-browserstack?style=flat-square)](https://github.com/creasico/laravel-dusk-browserstack/blob/master/LICENSE)

# Additional BrowserStack Supports for Laravel Dusk

**WIP**

## Installation

Use [Composer](https://getcomposer.org/)

```bash
$ composer require creasi/dusk-browserstack --dev
```

## Usage

1. Add `SupportsBrowsersStack` to your existing `DuskTestCase`, like so

   ```diff
     namespace Tests;
  
     use Laravel\Dusk\TestCase as BaseTestCase;
   + use Creasi\DuskBrowserStack\SupportsBrowserStack;
   
     abstract class DuskTestCase extends BaseTestCase
     {
         use CreatesApplication;
   +     use SupportsBrowserStack;
  
         // ...
     }
   ```

2. Update `prepare` method

   ```diff
     public static function prepare()
     {
   +     if (static::hasBrowserStackKey()) {
   +         static::startBrowserStackLocal();
   +         return;
   +     }
   
         if (! static::runningInSail()) {
             static::startChromeDriver();
         }
     }
   ```

1. Update `driver` method

     ```diff
     protected function driver()
     {
         // ...

    +   $capabilities = DesiredCapabilities::chrome()
    +       ->setCapability(ChromeOptions::CAPABILITY, $options);
         
         return RemoteWebDriver::create(
    -       $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
    -       DesiredCapabilities::chrome()->setCapability(
    -           ChromeOptions::CAPABILITY, $options
    -       )
    +       $this->getDriverURL(),
    +       $this->withBrowserStackCapabilities($capabilities)
         );
     }
     ```
## License

This library is open-sourced software licensed under [MIT license](LICENSE).
