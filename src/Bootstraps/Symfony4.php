<?php

namespace PHPPM\Bootstraps;

use PHPPM\Bootstraps\Symfony as BaseSymfony;
use PHPPM\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Config\Resource\FileResource;
use function PHPPM\register_file;

/**
 * Symfony4
 *
 * Adds auto-kernel discovery and overridable bootstrap methods instead of the monolithic
 * getApplication from the standard Symfony bootstrap.
 *
 * Additionally: will utilise loadEnv if available for handling environment overrides.
 */
class Symfony4 extends BaseSymfony
{

    /**
     * Create a Symfony application
     *
     * @return KernelInterface
     */
    public function getApplication()
    {
        $app = $this->createKernelInstance();

        $this->initializeKernel($app);
        $this->bootKernel($app);

        return $app;
    }

    /**
     * @return KernelInterface
     */
    protected function createKernelInstance()
    {
        require $this->getVendorDir().'/autoload.php';

        // attempt to preload the environment vars
        $this->loadEnvironmentVariables();

        // locate and attempt to boot the kernel in the current project folder
        $kernel = $this->locateApplicationKernel();

        return new $kernel($this->appenv, $this->debug);
    }

    /**
     * @param KernelInterface $app
     */
    protected function initializeKernel($app)
    {
        // we need to change some services, before the boot, because they would otherwise
        // be instantiated and passed to other classes which makes it impossible to replace them.
        Utils::bindAndCall(function () use ($app) {
            $app->initializeBundles();
            $app->initializeContainer();
        }, $app);
    }

    /**
     * @param KernelInterface $app
     */
    protected function bootKernel($app)
    {
        Utils::bindAndCall(function () use ($app) {
            foreach ($app->getBundles() as $bundle) {
                $bundle->setContainer($app->container);
                $bundle->boot();
            }

            $app->booted = true;
        }, $app);

        if ($this->debug) {
            Utils::bindAndCall(function () use ($app) {
                $container = $app->container;

                $containerClassName = \substr(\strrchr(\get_class($app->container), "\\"), 1);
                $metaName = $containerClassName . '.php.meta';

                Utils::bindAndCall(function () use ($container) {
                    $container->publicContainerDir = $container->containerDir;
                }, $container);

                if ($container->publicContainerDir === null) {
                    return;
                }

                $metaContent = @\file_get_contents($app->container->publicContainerDir . '/../' . $metaName);

                // Cannot read the Metadata, returning
                if ($metaContent === false) {
                    return;
                }

                $containerMetadata = \unserialize($metaContent);

                foreach ($containerMetadata as $entry) {
                    if ($entry instanceof FileResource) {
                        register_file($entry->__toString());
                    }
                }
            }, $app);
        }

        if ($trustedProxies = \getenv('TRUSTED_PROXIES')) {
            Request::setTrustedProxies(\explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
        }

        if ($trustedHosts = \getenv('TRUSTED_HOSTS')) {
            Request::setTrustedHosts(\explode(',', $trustedHosts));
        }
    }

    /**
     * Attempt to load the env vars from .env, only if Dotenv exists
     */
    protected function loadEnvironmentVariables()
    {
        if (!\getenv('APP_ENV') && \class_exists('Symfony\Component\Dotenv\Dotenv')) {
            $env = new \Symfony\Component\Dotenv\Dotenv();

            if (\method_exists($env, 'loadEnv')) {
                $env->loadEnv(\realpath('./.env'));
            } else {
                $env->load(\realpath('./.env'));
            }
        }
    }

    /**
     * Based on getNamespace from Illuminate\Foundation\Application
     *
     * @return string
     */
    protected function locateApplicationKernel()
    {
        $composer = \json_decode(\file_get_contents(\realpath('./composer.json')), true);

        if (!isset($composer['autoload']['psr-4'])) {
            return $this->guessDefaultKernelClass();
        }

        foreach ((array) $composer['autoload']['psr-4'] as $namespace => $path) {
            if (\strlen($namespace) > 0) {
                foreach ((array)$path as $pathChoice) {
                    if (\realpath('./src/') == \realpath('./' . $pathChoice)) {
                        return $namespace . 'Kernel';
                    }
                }
            }
        }

        return $this->guessDefaultKernelClass();
    }

    /**
     * @return string
     */
    protected function guessDefaultKernelClass()
    {
        return \class_exists('AppKernel') ? 'AppKernel' : 'App\Kernel';
    }
}
