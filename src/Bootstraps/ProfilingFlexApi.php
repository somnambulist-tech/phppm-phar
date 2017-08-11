<?php

namespace PHPPM\Bootstraps;

use PHPPM\Bootstraps\Behaviours\TidewaysProfiler;

/**
 * Class ProfilingFlexApi
 *
 * @package    PHPPM\Bootstraps
 * @subpackage PHPPM\Bootstraps\ProfilingFlexApi
 */
class ProfilingFlexApi extends SymfonyFlexApi
{

    use TidewaysProfiler;
    
}
