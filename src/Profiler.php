<?php

declare(strict_types=1);

namespace jubianchi\ProfIt;

use jubianchi\ProfIt\Profiler\Filter;

class Profiler
{
    private static $started = false;

    /**
     * @var Filter
     */
    private $filter;

    public function excludeNamespaces(string ...$namespaces): self
    {
        $profiler = clone $this;

        if (null === $this->filter) {
            $profiler->filter = new Filter();
        }

        foreach ($namespaces as $namespace) {
            $profiler->filter = $profiler->filter->withExcludedNamespace($namespace);
        }

        return $profiler;
    }

    public function includeNamespaces(string ...$namespaces): self
    {
        $profiler = clone $this;

        if (null === $this->filter) {
            $profiler->filter = new Filter();
        }

        foreach ($namespaces as $namespace) {
            $profiler->filter = $profiler->filter->withIncludedNamespace($namespace);
        }

        return $profiler;
    }

    public function start(int $flags = null): self
    {
        self::canStart();

        tideways_xhprof_enable($flags | TIDEWAYS_XHPROF_FLAGS_MEMORY | TIDEWAYS_XHPROF_FLAGS_CPU);

        self::$started = true;

        return $this;
    }

    public function stop(): Profile
    {
        self::canStop();

        $data = tideways_xhprof_disable();
        $profile = new Profile([
            'php' => PHP_VERSION,
            'osf' => PHP_OS_FAMILY,
            'os' => PHP_OS,
            'sapi' => PHP_SAPI,
            'extensions' => [
                'xdebug' => extension_loaded('xdebug'),
                'opcache' => extension_loaded('Zend OPcache'),
            ],
            'profile' => null === $this->filter ? $data : $this->filter->filter($data),
        ]);

        self::$started = false;

        return $profile;
    }

    public function profile(callable $code, int $flags = null): Profile
    {
        $this->start($flags);

        $code();

        return $this->stop();
    }

    private static function isExtensionLoaded()
    {
        if (!extension_loaded('tideways_xhprof')) {
            throw new \BadMethodCallException('Tideways XHProf is not enabled');
        }
    }

    private static function canStart()
    {
        if (self::$started) {
            throw new \BadMethodCallException('Profiler has already been started');
        }

        self::isExtensionLoaded();
    }

    private static function canStop()
    {
        if (!self::$started) {
            throw new \BadMethodCallException('Profiler has not been started');
        }
    }
}
