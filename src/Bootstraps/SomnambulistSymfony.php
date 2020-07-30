<?php

namespace PHPPM\Bootstraps;

use PHPPM\Utils;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use function class_exists;
use function explode;
use function file_get_contents;
use function get_class;
use function getenv;
use function json_decode;
use function PHPPM\register_file;
use function realpath;
use function strlen;
use function strrchr;
use function substr;
use function unserialize;

/**
 * SomnambulistSymfony
 *
 * Adds auto-kernel discovery and overridable bootstrap methods instead of the monolithic
 * getApplication from the standard Symfony bootstrap.
 *
 * Additionally: will utilise loadEnv if available for handling environment overrides.
 */
class SomnambulistSymfony extends Symfony
{

    /**
     * Create a Symfony application
     *
     * @return KernelInterface
     */
    public function getApplication()
    {
        $app = $this->createKernelInstance();

        $this->bootKernel($app);

        return $app;
    }

    /**
     * @return KernelInterface
     */
    protected function createKernelInstance()
    {
        require realpath($this->getVendorDir() . '/autoload.php');

        $this->loadEnvironmentVariables();

        $kernel = $this->locateApplicationKernel();

        return new $kernel($this->appenv, $this->debug);
    }

    /**
     * @param KernelInterface $app
     */
    protected function bootKernel($app)
    {
        if ($this->debug) {
            Utils::bindAndCall(function () use ($app) {
                $app->boot();
                $container = $app->container;

                $containerClassName = substr(strrchr(get_class($container), "\\"), 1);
                $metaName           = $containerClassName . '.php.meta';

                Utils::bindAndCall(function () use ($container) {
                    $container->publicContainerDir = $container->containerDir;
                }, $container);

                if ($container->publicContainerDir === null) {
                    return;
                }

                $metaContent = @file_get_contents($app->container->publicContainerDir . '/../' . $metaName);

                // Cannot read the Metadata, returning
                if ($metaContent === false) {
                    return;
                }

                $containerMetadata = unserialize($metaContent);

                foreach ($containerMetadata as $entry) {
                    if ($entry instanceof FileResource) {
                        register_file($entry->__toString());
                    }
                }
            }, $app);
        }

        if ($trustedProxies = getenv('TRUSTED_PROXIES')) {
            Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
        }

        if ($trustedHosts = getenv('TRUSTED_HOSTS')) {
            Request::setTrustedHosts(explode(',', $trustedHosts));
        }
    }

    protected function loadEnvironmentVariables(): void
    {
        if (!getenv('APP_ENV') && class_exists(Dotenv::class) && file_exists(realpath('.env'))) {
            //Symfony >=5.1 compatibility
            if (method_exists(Dotenv::class, 'usePutenv')) {
                (new Dotenv())->usePutenv()->bootEnv(realpath('.env'));
            } else {
                (new Dotenv(true))->loadEnv(realpath('.env'));
            }
        }
    }

    /**
     * Based on getNamespace from Illuminate\Foundation\Application
     */
    protected function locateApplicationKernel(): string
    {
        $composer = json_decode(file_get_contents(realpath('./composer.json')), true);

        if (!isset($composer['autoload']['psr-4'])) {
            return $this->guessDefaultKernelClass();
        }

        foreach ((array)$composer['autoload']['psr-4'] as $namespace => $path) {
            if (strlen($namespace) > 0) {
                foreach ((array)$path as $pathChoice) {
                    if (realpath('./src/') == realpath('./' . $pathChoice)) {
                        return $namespace . 'Kernel';
                    }
                }
            }
        }

        return $this->guessDefaultKernelClass();
    }

    protected function guessDefaultKernelClass(): string
    {
        return class_exists('AppKernel') ? 'AppKernel' : 'App\Kernel';
    }
}
