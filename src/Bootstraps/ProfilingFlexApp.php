<?php

namespace PHPPM\Bootstraps;

use PHPPM\Bootstraps\Behaviours\TidewaysProfiler;

/**
 * Class ProfilingFlexApp
 *
 * @package    PHPPM\Bootstraps
 * @subpackage PHPPM\Bootstraps\ProfilingFlexApp
 */
class ProfilingFlexApp extends SymfonyFlexApp
{

    use TidewaysProfiler;

}
