# Phar compiler for php-pm

[![PPM Compatible](https://raw.githubusercontent.com/php-pm/ppm-badge/master/ppm-badge.png)](https://github.com/php-pm/php-pm)

Adds a compiler derived from Composers Composer\Compiler class that creates a ppm.phar file for use
in other projects, isolating the current dev-master nature of php-pm from your project.

This library includes:

 * httpkernel-adapter
 * zend-adapter
 * drupal-adapter
 * psr7-adapter

Symfony has been pinned to the current stable 3.3.X branches.

In addition the following additional bootstrappers are included:

 * `SymfonyFlexApp`
 
   General bootstrapper for SF Flex based projects that can handle the change in Kernel naming
   and the new public folder. **Note:** Kernel discovery requires the usage of PSR-4 naming conventions
   in your project and that the standard `./src` folder is being used. If not found, `AppKernel` is
   used as a fallback.
 
 * `SymfonyFlexApi`
 
   A special bootstrapper that completely ignores doing anything with sessions because it's an API
   and a session is not needed so the StrongerNativeSessionStorage is not mapped.

 * `ProfilingFlexApp`
 
   Adds support for Tideways Profiling in the bootstrapper for Applications.
   
 * `ProfilingFlexApi`
 
   Adds support for Tideways Profiling in the bootstrapper for API kernels.

These extend from the base Symfony bootstrapper.

In addition: the env var APP_ENV is checked, and if `Symfony\Component\Dotenv\Dotenv` is available,
the default .env file will be loaded before the Kernel is booted.

To use any of these Bootstrap classes, replace your bootstrap in the ppm.json with the class
name. They use the `PHPPM\Bootstrappers` namespace for autoloading.

### Compile Phar

Run: `./bin/compile` - a `ppm.phar` will be created in the root folder.

Copy the ppm.phar wherever you would like or symlink it: e.g.: `ln -s ./ppm.phar /usr/local/bin/ppm`

If you have specific version requirements, clone this package and set the versions you need.
