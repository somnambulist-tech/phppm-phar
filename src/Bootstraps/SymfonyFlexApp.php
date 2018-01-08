<?php

namespace PHPPM\Bootstraps;

use PHPPM\Symfony\StrongerNativeSessionStorage;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * SymfonyFlexApp
 *
 * Custom bootstrapper to handle the changes introduced in Symfony Flex.
 * Attempts to locate the kernel in the default src/ folder.
 */
class SymfonyFlexApp extends AbstractSymfony
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

        // replace session handler with one more suited to php-pm (from Symfony bootstrapper)
        if ($app->getContainer()->hasParameter('session.storage.options')) {
            $nativeStorage = new StrongerNativeSessionStorage(
                $app->getContainer()->getParameter('session.storage.options'),
                $app->getContainer()->has('session.handler') ? $app->getContainer()->get('session.handler') : null
            );
            $app->getContainer()->set('session.storage.native', $nativeStorage);
        }

        $this->bootKernel($app);

        return $app;
    }
}
