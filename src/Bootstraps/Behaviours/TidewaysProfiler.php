<?php

namespace PHPPM\Bootstraps\Behaviours;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Trait TidewaysProfiler
 *
 * @package    PHPPM\Bootstraps\Behaviours
 * @subpackage PHPPM\Bootstraps\Behaviours\TidewaysProfiler
 */
trait TidewaysProfiler
{

    /**
     * Start Tideways profiling
     *
     * @param KernelInterface $app
     */
    public function preHandle($app)
    {
        if (class_exists('Tideways\Profiler')) {
            \Tideways\Profiler::start();
        }

        parent::preHandle($app);
    }

    /**
     * Stop Tideways profiling
     *
     * @param KernelInterface $app
     */
    public function postHandle($app)
    {
        parent::postHandle($app);

        if (class_exists('Tideways\Profiler')) {
            \Tideways\Profiler::stop();
        }
    }
}
