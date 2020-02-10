<?php

namespace PHPPM\Bootstraps;

/**
 * Symfony4
 *
 * Adds auto-kernel discovery and overridable bootstrap methods instead of the monolithic
 * getApplication from the standard Symfony bootstrap.
 *
 * Additionally: will utilise loadEnv if available for handling environment overrides.
 *
 * @deprecated Use SomnambulistSymfony instead. Symfony4 adapter will be removed as builds
 *             now target Symfony 5.
 */
class Symfony4 extends SomnambulistSymfony
{

}
