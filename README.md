# Phar compiler for php-pm

[![GitHub Actions release Build Status](https://github.com/dave-redfern/somnambulist-phppm-phar/workflows/release/badge.svg)](https://github.com/dave-redfern/somnambulist-phppm-phar/actions?query=workflow%3Arelease)
[![PPM Compatible](https://raw.githubusercontent.com/php-pm/ppm-badge/master/ppm-badge.png)](https://github.com/php-pm/php-pm)

Adds a compiler derived from Composers Composer\Compiler class that creates a ppm.phar file for use
in other projects, isolating the php-pm dependencies from your project making it more portable.

Note: this library does not follow any versioning scheme.

This library includes:

 * httpkernel-adapter
 
Note: all other adaptors were removed as they are not being frequently updated.

In addition the following additional bootstrappers are included:

 * `Symfony4`
 
   General bootstrapper for SF 4+ based projects that can handle the change in Kernel naming
   and the new public folder. **Note:** Kernel discovery requires the usage of PSR-4 naming
   conventions in your project and that the standard `./src` folder is being used. If not
   found, `AppKernel` and `App\Kernel` will be used as fallbacks.
   
   Unlike the standard Symfony bootstrap, this one can handle .env.local and other overrides.

In addition: the env var APP_ENV is checked, and if `Symfony\Component\Dotenv\Dotenv` is available,
the default .env file will be loaded before the Kernel is booted.

To use any of these Bootstrap classes, replace your bootstrap in the ppm.json with the class
name. They use the `PHPPM\Bootstrappers` namespace for autoloading.

### Compile Phar

Run: `./bin/compile` - a `ppm.phar` will be created in the root folder.

Copy the ppm.phar wherever you would like or symlink it: e.g.: `ln -s ./ppm.phar /usr/local/bin/ppm`

If you have specific version requirements, clone this package and set the versions you need.

### Major Changes

#### 2020-02-05

Added SomnambulistSymfony adapter to replace Symfony4 (now deprecated).

#### 2020-02-05

Release against Symfony 5.0.4 dependencies (4.X)

#### 2020-01-29

Updated to PHP-PM 2.0.3

#### 2019-11-22

Release against Symfony 4.4 dependencies (3.x)

#### 2019-05-10

Updated to PHP-PM 2.0.0 / HttpKernel 2.0.1
Removed SymfonyFlex / SymfonyFlexApi classes
Renamed AbstractSymfony to Symfony4

#### 2018-12-09

Updated to PHP-PM 1.0.5
Removed SF 3.3 support

#### 2018-01-08

Switched to stable libraries and removed the Tideways additions.

#### 2017-07-16

Initial commit
