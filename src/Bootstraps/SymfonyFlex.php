<?php

namespace PHPPM\Bootstraps;

use PHPPM\Bootstraps\Symfony as BaseSymfony;
use PHPPM\Symfony\StrongerNativeSessionStorage;
use PHPPM\Utils;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * SymfonyFlex
 *
 * Custom bootstrapper to handle the changes introduced in Symfony Flex.
 * Attempts to locate the kernel in the default src/ folder.
 */
class SymfonyFlex extends BaseSymfony
{
    /**
     * @return string
     */
    public function getStaticDirectory()
    {
        return 'public/';
    }

    /**
     * Create a Symfony application
     *
     * @return KernelInterface
     */
    public function getApplication()
    {
        // include applications autoload
        require './vendor/autoload.php';

        // attempt to preload the environment vars
        $this->loadEnvironmentVariables();

        // locate and attempt to boot the kernel in the current project folder
        $kernel = $this->locateApplicationKernel();
        $app    = new $kernel($this->appenv, $this->debug);

        // we need to change some services, before the boot, because they would otherwise
        // be instantiated and passed to other classes which makes it impossible to replace them.
        Utils::bindAndCall(function () use ($app) {
            $app->initializeBundles();
            $app->initializeContainer();
        }, $app);

        // replace session handler with one more suited to php-pm (from Symfony bootstrapper)
        if ($app->getContainer()->hasParameter('session.storage.options')) {
            $nativeStorage = new StrongerNativeSessionStorage(
                $app->getContainer()->getParameter('session.storage.options'),
                $app->getContainer()->has('session.handler') ? $app->getContainer()->get('session.handler') : null,
                $app->getContainer()->get('session.storage.metadata_bag')
            );
            $app->getContainer()->set('session.storage.native', $nativeStorage);
        }

        Utils::bindAndCall(function () use ($app) {
            foreach ($app->getBundles() as $bundle) {
                $bundle->setContainer($app->container);
                $bundle->boot();
            }

            $app->booted = true;
        }, $app);

        return $app;
    }

    /**
     * Attempt to load the env vars from .env, only if Dotenv exists
     */
    protected function loadEnvironmentVariables()
    {
        if (!getenv('APP_ENV') && class_exists('Symfony\Component\Dotenv\Dotenv')) {
            (new \Symfony\Component\Dotenv\Dotenv())->load(realpath('./.env'));
        }
    }

    /**
     * Based on getNamespace from Illuminate\Foundation\Application
     *
     * @return string
     */
    protected function locateApplicationKernel()
    {
        $composer = json_decode(file_get_contents(realpath('./composer.json')), true);

        if (!isset($composer['autoload']['psr-4'])) {
            return 'AppKernel';
        }

        foreach ((array) $composer['autoload']['psr-4'] as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath('./src/') == realpath('./'.$pathChoice)) {
                    return $namespace . 'Kernel';
                }
            }
        }

        return 'AppKernel';
    }
}
