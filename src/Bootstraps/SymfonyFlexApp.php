<?php

namespace PHPPM\Bootstraps;

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
        $this->bootKernel($app);

        return $app;
    }
}
