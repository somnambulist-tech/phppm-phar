<?php

namespace PHPPM;

use Symfony\Component\Finder\Finder;
use function shell_exec;

/**
 * Compiler
 *
 * Shamelessly ripped from Composer.
 *
 * @link https://github.com/composer/composer/blob/master/src/Composer/Compiler.php
 */
class Compiler
{

    /**
     * Compiles PPM into a single phar file
     *
     * @param string $pharFile The full path to the file to create
     *
     * @throws \RuntimeException
     */
    public function compile($pharFile = 'ppm.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'ppm.phar');
        $phar->startBuffering();

        $finderSort = function ($a, $b) {
            return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
        };

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('LICENSE')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->notName('create-phar.php')
            ->notName('Compiler.php')
            ->in(__DIR__ . '/../src/')
            ->in(__DIR__ . '/../vendor/symfony/')

            ->in(__DIR__ . '/../vendor/evenement/')
            ->in(__DIR__ . '/../vendor/guzzlehttp/')
            ->in(__DIR__ . '/../vendor/monolog/')
            ->in(__DIR__ . '/../vendor/php-pm/')
            ->in(__DIR__ . '/../vendor/psr/')
            ->in(__DIR__ . '/../vendor/ralouphie/')
            ->in(__DIR__ . '/../vendor/react/')
            ->in(__DIR__ . '/../vendor/ringcentral/')
            ->sort($finderSort)
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/autoload.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_psr4.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_files.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_real.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_static.php'));
        if (file_exists(__DIR__ . '/../vendor/composer/include_paths.php')) {
            $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/include_paths.php'));
        }
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/ClassLoader.php'));

        $this->addBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        unset($phar);

        chmod($pharFile, 0755);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = strtr(str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $file->getRealPath()), '\\', '/');

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n" . $content . "\n";
        }

        $phar->addFromString($path, $content);
    }

    private function addBin($phar)
    {
        $content = file_get_contents(__DIR__ . '/../bin/run');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/ppm', $content);
    }

    private function getMostRecentTagFromRepository()
    {
        return shell_exec('git describe --abbrev=0 --tags') ?? 'latest';
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output     .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php

// Copied from Composer stub - not sure if this will impact php-pm, but better safe!
// Avoid APC causing random fatal errors per https://github.com/composer/composer/issues/264
if (extension_loaded('apc') && ini_get('apc.enable_cli') && ini_get('apc.cache_by_default')) {
    if (version_compare(phpversion('apc'), '3.0.12', '>=')) {
        ini_set('apc.cache_by_default', 0);
    } else {
        fwrite(STDERR, 'Warning: APC <= 3.0.12 may cause fatal errors when running ppm commands.'.PHP_EOL);
        fwrite(STDERR, 'Update APC, or set apc.enable_cli or apc.cache_by_default to 0 in your php.ini.'.PHP_EOL);
    }
}

Phar::mapPhar('ppm.phar');
require 'phar://ppm.phar/bin/ppm';

__HALT_COMPILER();
EOF;
    }
}
