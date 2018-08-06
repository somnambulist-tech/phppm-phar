# Phar compiler for php-pm

[![PPM Compatible](https://raw.githubusercontent.com/php-pm/ppm-badge/master/ppm-badge.png)](https://github.com/php-pm/php-pm)

Adds a compiler derived from Composers Composer\Compiler class that creates a ppm.phar file for use
in other projects, isolating the php-pm dependencies from your project making it more portable.

This library includes:

 * httpkernel-adapter
 
Note: all other adaptors were removed as they are not being frequently updated.

In addition the following additional bootstrappers are included:

 * `SymfonyFlexApp`
 
   General bootstrapper for SF Flex based projects that can handle the change in Kernel naming
   and the new public folder. **Note:** Kernel discovery requires the usage of PSR-4 naming conventions
   in your project and that the standard `./src` folder is being used. If not found, `AppKernel`
   and `App\Kernel` will be used as fallbacks.
 
 * `SymfonyFlexApi`
 
   A special bootstrapper that completely ignores doing anything with sessions.
   As of 2018-08-06 this is functionally the same as the App adapter.

These extend from the base Symfony bootstrapper.

In addition: the env var APP_ENV is checked, and if `Symfony\Component\Dotenv\Dotenv` is available,
the default .env file will be loaded before the Kernel is booted.

To use any of these Bootstrap classes, replace your bootstrap in the ppm.json with the class
name. They use the `PHPPM\Bootstrappers` namespace for autoloading.

### Compile Phar

Run: `./bin/compile` - a `ppm.phar` will be created in the root folder.

Copy the ppm.phar wherever you would like or symlink it: e.g.: `ln -s ./ppm.phar /usr/local/bin/ppm`

If you have specific version requirements, clone this package and set the versions you need.
