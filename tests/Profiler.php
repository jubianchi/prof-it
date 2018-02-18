<?php

declare(strict_types=1);

namespace jubianchi\ProfIt\Tests\Unit;

use BadMethodCallException;
use jubianchi\ProfIt\Profile;
use mageekguy\atoum;

class Profiler extends atoum
{
    public function beforeTestMethod($_)
    {
        $this->function->tideways_xhprof_enable->doesNothing;
        $this->function->tideways_xhprof_disable = [];
    }

    public function testStart()
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->object($this->testedInstance->start())->isTestedInstance
                ->function('tideways_xhprof_enable')->wasCalled->once
        ;
    }

    public function testStartWithoutExtension()
    {
        $this
            ->given(
                $this->function->extension_loaded = false,
                $this->newTestedInstance
            )
            ->then
                ->exception(function () {
                    $this->testedInstance->start();
                })
                    ->isInstanceOf(BadMethodCallException::class)
                    ->hasMessage('Tideways XHProf is not enabled')
        ;
    }

    public function testStartOnlyOnce()
    {
        $this
            ->given($this->newTestedInstance)
            ->if($this->testedInstance->start())
            ->then
                ->exception(function () {
                    $this->testedInstance->start();
                })
                    ->isInstanceOf(BadMethodCallException::class)
                    ->hasMessage('Profiler has already been started')
        ;
    }

    public function testStop()
    {
        $this
            ->given($this->newTestedInstance)
            ->if($this->testedInstance->start())
            ->then
                ->object($this->testedInstance->stop())->isInstanceOf(Profile::class)
                ->function('tideways_xhprof_disable')->wasCalled->once
        ;
    }

    public function testStopNotStarted()
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->exception(function () {
                    $this->testedInstance->stop();
                })
                    ->isInstanceOf(BadMethodCallException::class)
                    ->hasMessage('Profiler has not been started')
        ;
    }

    public function testProfile()
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->object($this->testedInstance->profile(function () {}))->isInstanceOf(Profile::class)
                ->function('tideways_xhprof_enable')->wasCalled->once
                ->function('tideways_xhprof_disable')->wasCalled->once
        ;
    }

    public function testProfileAlreadyStarted()
    {
        $this
            ->given($this->newTestedInstance)
            ->if($this->testedInstance->start())
            ->then
                ->exception(function () {
                    $this->testedInstance->profile(function () {});
                })
                    ->isInstanceOf(BadMethodCallException::class)
                    ->hasMessage('Profiler has already been started')
        ;
    }
}
