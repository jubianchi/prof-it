<?php

declare(strict_types=1);

namespace jubianchi\ProfIt\Tests\Unit;

use mageekguy\atoum;

class Profile extends atoum
{
    /**
     * @var string
     */
    private $date;

    public function beforeTestMethod($_)
    {
        $this->function->date = $this->date = date(DATE_ISO8601);
    }

    public function testEmptyProfile()
    {
        $this
            ->given(
                $profile = $this->newTestedInstance([]),
                $name = uniqid()
            )
            ->then
            ->array($this->testedInstance->dump($name))->isEqualTo([
                'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                'name' => $name,
                'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                'hash' => '1777238b3d11ad84c9fea42dd12a28b7',
                'php' => null,
                'osf' => null,
                'os' => null,
                'sapi' => null,
                'extensions' => [
                    'xdebug' => null,
                    'opcache' => null,
                ],
                'functions' => [],
                'calls' => [],
            ])
        ;
    }

    /**
     * main() --> A.
     */
    public function testOneCallee()
    {
        $this
            ->given(
                $data = [
                    'main()' => [
                        'ct' => 1,
                        'wt' => 100,
                        'cpu' => 96,
                        'mu' => 100,
                        'pmu' => 0,
                    ],
                    'main()==>A' => [
                        'ct' => 1,
                        'wt' => 60,
                        'cpu' => 48,
                        'mu' => 90,
                        'pmu' => 0,
                    ],
                ],
                $profile = $this->newTestedInstance(['profile' => $data]),
                $name = uniqid()
            )
            ->then
                ->array($this->testedInstance->dump($name))->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $name,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => 'c812cd049fd45dba6b79e12127dfc1c1',
                    'php' => null,
                    'osf' => null,
                    'os' => null,
                    'sapi' => null,
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'functions' => [
                        [
                            'id' => 0,
                            'label' => 'main()',
                            'ct' => $data['main()']['ct'],
                            'wt' => $data['main()']['wt'],
                            'wte' => $data['main()']['wt'] - $data['main()==>A']['wt'],
                            'cpu' => $data['main()']['cpu'],
                            'mu' => $data['main()']['mu'],
                            'pmu' => $data['main()']['pmu'],
                            'wtip' => 1,
                            'wtep' => round(($data['main()']['wt'] - $data['main()==>A']['wt']) / $data['main()']['wt'], 3),
                            'cpup' => 1,
                        ],
                        [
                            'id' => 4,
                            'label' => 'A',
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'wte' => $data['main()==>A']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu'],
                            'wtip' => round($data['main()==>A']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round($data['main()==>A']['wt'] / $data['main()']['wt'], 3),
                            'cpup' => round($data['main()==>A']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                    ],
                    'calls' => [
                        [
                            'from' => 0,
                            'to' => 4,
                            'value' => round($data['main()==>A']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu'],
                        ],
                    ],
                ])
        ;
    }

    /**
     *          +--> A --+
     *          |        |
     * main() --+        +--> C
     *          |        |
     *          +--> B --+.
     */
    public function testTwoCallersOneCallee()
    {
        $this
            ->given(
                $data = [
                    'main()' => [
                        'ct' => 1,
                        'wt' => 100,
                        'cpu' => 90,
                        'mu' => 100,
                        'pmu' => 0,
                    ],
                    'main()==>A' => [
                        'ct' => 1,
                        'wt' => 40,
                        'cpu' => 10,
                        'mu' => 20,
                        'pmu' => 0,
                    ],
                    'main()==>B' => [
                        'ct' => 1,
                        'wt' => 30,
                        'cpu' => 15,
                        'mu' => 20,
                        'pmu' => 0,
                    ],
                    'A==>C' => [
                        'ct' => 1,
                        'wt' => 20,
                        'cpu' => 10,
                        'mu' => 10,
                        'pmu' => 0,
                    ],
                    'B==>C' => [
                        'ct' => 1,
                        'wt' => 20,
                        'cpu' => 10,
                        'mu' => 10,
                        'pmu' => 0,
                    ],
                ],
                $profile = $this->newTestedInstance(['profile' => $data]),
                $name = uniqid()
            )
            ->then
                ->array($this->testedInstance->dump($name))->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $name,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '92c46e1e0bef110971c3458acdf3478c',
                    'php' => null,
                    'osf' => null,
                    'os' => null,
                    'sapi' => null,
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'functions' => [
                        [
                            'id' => 0,
                            'label' => 'main()',
                            'ct' => $data['main()']['ct'],
                            'wt' => $data['main()']['wt'],
                            'wte' => $data['main()']['wt'] - $data['main()==>A']['wt'] - $data['main()==>B']['wt'],
                            'cpu' => $data['main()']['cpu'],
                            'mu' => $data['main()']['mu'],
                            'pmu' => $data['main()']['pmu'],
                            'wtip' => round($data['main()']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round(($data['main()']['wt'] - $data['main()==>A']['wt'] - $data['main()==>B']['wt']) / $data['main()']['wt'], 3),
                            'cpup' => round($data['main()']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                        [
                            'id' => 4,
                            'label' => 'A',
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'wte' => $data['main()==>A']['wt'] - $data['A==>C']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu'],
                            'wtip' => round($data['main()==>A']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round(($data['main()==>A']['wt'] - $data['A==>C']['wt']) / $data['main()']['wt'], 3),
                            'cpup' => round($data['main()==>A']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                        [
                            'id' => 5,
                            'label' => 'B',
                            'ct' => $data['main()==>B']['ct'],
                            'wt' => $data['main()==>B']['wt'],
                            'wte' => $data['main()==>B']['wt'] - $data['B==>C']['wt'],
                            'cpu' => $data['main()==>B']['cpu'],
                            'mu' => $data['main()==>B']['mu'],
                            'pmu' => $data['main()==>B']['pmu'],
                            'wtip' => round($data['main()==>B']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round(($data['main()==>B']['wt'] - $data['B==>C']['wt']) / $data['main()']['wt'], 3),
                            'cpup' => round($data['main()==>B']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                        [
                            'id' => 6,
                            'label' => 'C',
                            'ct' => $data['A==>C']['ct'] + $data['B==>C']['ct'],
                            'wt' => $data['A==>C']['wt'] + $data['B==>C']['wt'],
                            'wte' => $data['A==>C']['wt'] + $data['B==>C']['wt'],
                            'cpu' => $data['A==>C']['cpu'] + $data['B==>C']['cpu'],
                            'mu' => $data['A==>C']['mu'] + $data['B==>C']['mu'],
                            'pmu' => $data['A==>C']['pmu'] + $data['B==>C']['pmu'],
                            'wtip' => round(($data['A==>C']['wt'] + $data['B==>C']['wt']) / $data['main()']['wt'], 3),
                            'wtep' => round(($data['A==>C']['wt'] + $data['B==>C']['wt']) / $data['main()']['wt'], 3),
                            'cpup' => round(($data['A==>C']['cpu'] + $data['B==>C']['cpu']) / $data['main()']['cpu'], 3),
                        ],
                    ],
                    'calls' => [
                        [
                            'from' => 0,
                            'to' => 4,
                            'value' => round($data['main()==>A']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu'],
                        ],
                        [
                            'from' => 0,
                            'to' => 5,
                            'value' => round($data['main()==>B']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['main()==>B']['ct'],
                            'wt' => $data['main()==>B']['wt'],
                            'cpu' => $data['main()==>B']['cpu'],
                            'mu' => $data['main()==>B']['mu'],
                            'pmu' => $data['main()==>B']['pmu'],
                        ],
                        [
                            'from' => 4,
                            'to' => 6,
                            'value' => round($data['A==>C']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['A==>C']['ct'],
                            'wt' => $data['A==>C']['wt'],
                            'cpu' => $data['A==>C']['cpu'],
                            'mu' => $data['A==>C']['mu'],
                            'pmu' => $data['A==>C']['pmu'],
                        ],
                        [
                            'from' => 5,
                            'to' => 6,
                            'value' => round($data['B==>C']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['B==>C']['ct'],
                            'wt' => $data['B==>C']['wt'],
                            'cpu' => $data['B==>C']['cpu'],
                            'mu' => $data['B==>C']['mu'],
                            'pmu' => $data['B==>C']['pmu'],
                        ],
                    ],
                ])
        ;
    }

    /**
     *                +--> B
     *                |
     * main() --> A --+
     *                |
     *                +--> C.
     */
    public function testOneCallerTwoCallees()
    {
        $this
            ->given(
                $data = [
                    'main()' => [
                        'ct' => 1,
                        'wt' => 100,
                        'cpu' => 96,
                        'mu' => 100,
                        'pmu' => 0,
                    ],
                    'main()==>A' => [
                        'ct' => 1,
                        'wt' => 60,
                        'cpu' => 48,
                        'mu' => 90,
                        'pmu' => 0,
                    ],
                    'A==>B' => [
                        'ct' => 1,
                        'wt' => 20,
                        'cpu' => 10,
                        'mu' => 10,
                        'pmu' => 0,
                    ],
                    'A==>C' => [
                        'ct' => 1,
                        'wt' => 20,
                        'cpu' => 10,
                        'mu' => 10,
                        'pmu' => 0,
                    ],
                ],
                $profile = $this->newTestedInstance(['profile' => $data]),
                $name = uniqid()
            )
            ->then
                ->array($this->testedInstance->dump($name))->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $name,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '4c9e2c06c6e8e34ec1b01f15fd3b6ec2',
                    'php' => null,
                    'osf' => null,
                    'os' => null,
                    'sapi' => null,
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'functions' => [
                        [
                            'id' => 0,
                            'label' => 'main()',
                            'ct' => $data['main()']['ct'],
                            'wt' => $data['main()']['wt'],
                            'wte' => $data['main()']['wt'] - $data['main()==>A']['wt'],
                            'cpu' => $data['main()']['cpu'],
                            'mu' => $data['main()']['mu'],
                            'pmu' => $data['main()']['pmu'],
                            'wtip' => round($data['main()']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round(($data['main()']['wt'] - $data['main()==>A']['wt']) / $data['main()']['wt'], 3),
                            'cpup' => round($data['main()']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                        [
                            'id' => 4,
                            'label' => 'A',
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'wte' => $data['main()==>A']['wt'] - $data['A==>B']['wt'] - $data['A==>C']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu'],
                            'wtip' => round($data['main()==>A']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round(($data['main()==>A']['wt'] - $data['A==>B']['wt'] - $data['A==>C']['wt']) / $data['main()']['wt'], 3),
                            'cpup' => round($data['main()==>A']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                        [
                            'id' => 5,
                            'label' => 'B',
                            'ct' => $data['A==>B']['ct'],
                            'wt' => $data['A==>B']['wt'],
                            'wte' => $data['A==>B']['wt'],
                            'cpu' => $data['A==>B']['cpu'],
                            'mu' => $data['A==>B']['mu'],
                            'pmu' => $data['A==>B']['pmu'],
                            'wtip' => round($data['A==>B']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round($data['A==>B']['wt'] / $data['main()']['wt'], 3),
                            'cpup' => round($data['A==>B']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                        [
                            'id' => 6,
                            'label' => 'C',
                            'ct' => $data['A==>C']['ct'],
                            'wt' => $data['A==>C']['wt'],
                            'wte' => $data['A==>C']['wt'],
                            'cpu' => $data['A==>C']['cpu'],
                            'mu' => $data['A==>C']['mu'],
                            'pmu' => $data['A==>C']['pmu'],
                            'wtip' => round($data['A==>C']['wt'] / $data['main()']['wt'], 3),
                            'wtep' => round($data['A==>C']['wt'] / $data['main()']['wt'], 3),
                            'cpup' => round($data['A==>C']['cpu'] / $data['main()']['cpu'], 3),
                        ],
                    ],
                    'calls' => [
                        [
                            'from' => 0,
                            'to' => 4,
                            'value' => round($data['main()==>A']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu'],
                        ],
                        [
                            'from' => 4,
                            'to' => 5,
                            'value' => round($data['A==>B']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['A==>B']['ct'],
                            'wt' => $data['A==>B']['wt'],
                            'cpu' => $data['A==>B']['cpu'],
                            'mu' => $data['A==>B']['mu'],
                            'pmu' => $data['A==>B']['pmu'],
                        ],
                        [
                            'from' => 4,
                            'to' => 6,
                            'value' => round($data['A==>C']['wt'] / $data['main()']['wt'], 3),
                            'ct' => $data['A==>C']['ct'],
                            'wt' => $data['A==>C']['wt'],
                            'cpu' => $data['A==>C']['cpu'],
                            'mu' => $data['A==>C']['mu'],
                            'pmu' => $data['A==>C']['pmu'],
                        ],
                    ],
                ])
        ;
    }

    public function testDumpDefaultName()
    {
        $this
            ->given(
                $this->function->time = $timestamp = time(),
                $profile = $this->newTestedInstance([]),
                $name = uniqid()
            )
            ->if($_SERVER['argv'] = null)
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => 'profile_'.$timestamp,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '1777238b3d11ad84c9fea42dd12a28b7',
                    'php' => null,
                    'osf' => null,
                    'os' => null,
                    'sapi' => null,
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'functions' => [],
                    'calls' => [],
                ])
        ;
    }

    public function testDumpNameFromCli()
    {
        $this
            ->given(
                $this->function->time = $timestamp = time(),
                $profile = $this->newTestedInstance([]),
                $name = uniqid()
            )
            ->if($_SERVER['argv'] = ['foo', 'bar'])
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => PHP_BINARY.' '.implode(' ', $_SERVER['argv']),
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '1777238b3d11ad84c9fea42dd12a28b7',
                    'php' => null,
                    'osf' => null,
                    'os' => null,
                    'sapi' => null,
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'functions' => [],
                    'calls' => [],
                ])
        ;
    }

    public function testDumpNameFromHttp()
    {
        $this
            ->given(
                $this->function->time = $timestamp = time(),
                $profile = $this->newTestedInstance([]),
                $name = uniqid()
            )
            ->if(
                $_SERVER['REQUEST_METHOD'] = 'GET',
                $_SERVER['REQUEST_URI'] = '/index.php',
                $_SERVER['HTTP_HOST'] = 'localhost'
            )
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $_SERVER['REQUEST_METHOD'].' http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '1777238b3d11ad84c9fea42dd12a28b7',
                    'php' => null,
                    'osf' => null,
                    'os' => null,
                    'sapi' => null,
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'functions' => [],
                    'calls' => [],
                ])
            ->if($_SERVER['HTTPS'] = 'yes')
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $_SERVER['REQUEST_METHOD'].' https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '1777238b3d11ad84c9fea42dd12a28b7',
                    'php' => null,
                    'osf' => null,
                    'os' => null,
                    'sapi' => null,
                    'functions' => [],
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'calls' => [],
                ])
        ;
    }

    public function testDumpPhpRuntimeEnv()
    {
        $this
            ->given(
                $data = [
                    'php' => $this->constant->PHP_VERSION = '7.1.2',
                    'osf' => $this->constant->PHP_OS_FAMILY = 'Linux',
                    'os' => $this->constant->PHP_OS = 'Ubuntu LTS',
                    'sapi' => $this->constant->PHP_SAPI = 'cli',
                    'profile' => [],
                ],
                $this->function->time = $timestamp = time(),
                $profile = $this->newTestedInstance($data),
                $name = uniqid()
            )
            ->if($_SERVER['argv'] = null)
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => 'profile_'.$timestamp,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '5e90ecfbd284cbad628543c1f4ece6b9',
                    'php' => $this->constant->PHP_VERSION ,
                    'osf' => $this->constant->PHP_OS_FAMILY,
                    'os' => $this->constant->PHP_OS,
                    'sapi' => $this->constant->PHP_SAPI,
                    'functions' => [],
                    'extensions' => [
                        'xdebug' => null,
                        'opcache' => null,
                    ],
                    'calls' => [],
                ])
        ;
    }



    public function testDumpPhpExtensions()
    {
        $this
            ->given(
                $data = [
                    'php' => $this->constant->PHP_VERSION = '7.1.2',
                    'osf' => $this->constant->PHP_OS_FAMILY = 'Linux',
                    'os' => $this->constant->PHP_OS = 'Ubuntu LTS',
                    'sapi' => $this->constant->PHP_SAPI = 'cli',
                    'extensions' => [
                        'xdebug' => false,
                        'opcache' => false
                    ],
                    'profile' => [],
                ],
                $this->function->time = $timestamp = time(),
                $profile = $this->newTestedInstance($data),
                $name = uniqid()
            )
            ->if($_SERVER['argv'] = null)
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => 'profile_'.$timestamp,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '7692c37f00d35a69c6d52e07bcd6c3ba',
                    'php' => $this->constant->PHP_VERSION ,
                    'osf' => $this->constant->PHP_OS_FAMILY,
                    'os' => $this->constant->PHP_OS,
                    'sapi' => $this->constant->PHP_SAPI,
                    'functions' => [],
                    'extensions' => [
                        'xdebug' => false,
                        'opcache' => false,
                    ],
                    'calls' => [],
                ])
            ->if(
                $data['extensions']['xdebug'] = true,
                $profile = $this->newTestedInstance($data)
            )
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => 'profile_'.$timestamp,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '5a3ec23a64079aec60b25306cc5ca446',
                    'php' => $this->constant->PHP_VERSION ,
                    'osf' => $this->constant->PHP_OS_FAMILY,
                    'os' => $this->constant->PHP_OS,
                    'sapi' => $this->constant->PHP_SAPI,
                    'functions' => [],
                    'extensions' => [
                        'xdebug' => true,
                        'opcache' => false,
                    ],
                    'calls' => [],
                ])
            ->if(
                $data['extensions']['opcache'] = true,
                $profile = $this->newTestedInstance($data)
                )
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => 'profile_'.$timestamp,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '7e0f4bc92bdbcd4a09ef7abb26a13a4c',
                    'php' => $this->constant->PHP_VERSION ,
                    'osf' => $this->constant->PHP_OS_FAMILY,
                    'os' => $this->constant->PHP_OS,
                    'sapi' => $this->constant->PHP_SAPI,
                    'functions' => [],
                    'extensions' => [
                        'xdebug' => true,
                        'opcache' => true,
                    ],
                    'calls' => [],
                ])
        ;
    }
}
