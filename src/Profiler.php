<?php

declare(strict_types=1);

namespace jubianchi\ProfIt;

class Profiler
{
    private static $started = false;

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

        $profile = new Profile(tideways_xhprof_disable());

        self::$started = false;

        return $profile;
    }

    public function profile(callable $code, int $flags = null): Profile
    {
        $this->start($flags);

        $code();

        return $this->stop();
    }

    private static function isExtensionLoaded(): void
    {
        if (!extension_loaded('tideways_xhprof')) {
            throw new \BadMethodCallException('Tideways XHProf is not enabled');
        }
    }

    private static function canStart(): void
    {
        self::isExtensionLoaded();

        if (self::$started) {
            throw new \BadMethodCallException('Profiler has already been started');
        }
    }

    private static function canStop(): void
    {
        self::isExtensionLoaded();

        if (!self::$started) {
            throw new \BadMethodCallException('Profiler has not been started');
        }
    }
}
