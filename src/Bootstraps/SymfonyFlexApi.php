<?php

namespace PHPPM\Bootstraps;

use PHPPM\Utils;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * SymfonyFlexApi
 *
 * Custom Symfony Flex bootstrapper that does nothing for sessions because they are not needed
 * for API only kernels.
 */
class SymfonyFlexApi extends SymfonyFlex
{

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

        //since we need to change some services, we need to manually change some services
        $kernel = $this->locateApplicationKernel();
        $app    = new $kernel($this->appenv, $this->debug);

        //we need to change some services, before the boot, because they would otherwise
        //be instantiated and passed to other classes which makes it impossible to replace them.
        Utils::bindAndCall(function () use ($app) {
            // init bundles
            $app->initializeBundles();

            // init container
            $app->initializeContainer();
        }, $app);

        Utils::bindAndCall(function () use ($app) {
            foreach ($app->getBundles() as $bundle) {
                $bundle->setContainer($app->container);
                $bundle->boot();
            }

            $app->booted = true;
        }, $app);

        return $app;
    }
}
