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
                'hash' => 'b49a6991f0854831529db58596424333',
                'functions' => [],
                'calls' => []
            ])
        ;
    }

    /**
     * main() --> A
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
                    ]
                ],
                $profile = $this->newTestedInstance($data),
                $name = uniqid()
            )
            ->then
            ->array($this->testedInstance->dump($name))->isEqualTo([
                'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                'name' => $name,
                'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                'hash' => 'd33eaf64ae2b34410cc30a1db06690d8',
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
                        'wtep' => ($data['main()']['wt'] - $data['main()==>A']['wt']) / $data['main()']['wt'],
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
                        'wtip' => $data['main()==>A']['wt'] / $data['main()']['wt'],
                        'wtep' => $data['main()==>A']['wt'] / $data['main()']['wt'],
                        'cpup' => $data['main()==>A']['cpu'] / $data['main()']['cpu'],
                    ],
                ],
                'calls' => [
                    [
                        'from' => 0,
                        'to' => 4,
                        'value' => $data['main()==>A']['wt'] / $data['main()']['wt'],
                        'ct' => $data['main()==>A']['ct'],
                        'wt' => $data['main()==>A']['wt'],
                        'cpu' => $data['main()==>A']['cpu'],
                        'mu' => $data['main()==>A']['mu'],
                        'pmu' => $data['main()==>A']['pmu'],
                    ]
                ]
            ])
        ;
    }

    /**
     *          +--> A --+
     *          |        |
     * main() --+        +--> C
     *          |        |
     *          +--> B --+
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
                $profile = $this->newTestedInstance($data),
                $name = uniqid()
            )
            ->then
                ->array($this->testedInstance->dump($name))->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $name,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '8f161e9ad97cae00e0d1b32851981391',
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
                            'wtip' => $data['main()']['wt'] / $data['main()']['wt'],
                            'wtep' => ($data['main()']['wt'] - $data['main()==>A']['wt'] - $data['main()==>B']['wt']) / $data['main()']['wt'],
                            'cpup' => $data['main()']['cpu'] / $data['main()']['cpu'],
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
                            'wtip' => $data['main()==>A']['wt'] / $data['main()']['wt'],
                            'wtep' => ($data['main()==>A']['wt'] - $data['A==>C']['wt']) / $data['main()']['wt'],
                            'cpup' => $data['main()==>A']['cpu'] / $data['main()']['cpu'],
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
                            'wtip' => $data['main()==>B']['wt'] / $data['main()']['wt'],
                            'wtep' => ($data['main()==>B']['wt'] - $data['B==>C']['wt']) / $data['main()']['wt'],
                            'cpup' => $data['main()==>B']['cpu'] / $data['main()']['cpu'],
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
                            'wtip' => ($data['A==>C']['wt'] + $data['B==>C']['wt']) / $data['main()']['wt'],
                            'wtep' => ($data['A==>C']['wt'] + $data['B==>C']['wt']) / $data['main()']['wt'],
                            'cpup' => ($data['A==>C']['cpu'] + $data['B==>C']['cpu']) / $data['main()']['cpu'],
                        ]
                    ],
                    'calls' => [
                        [
                            'from' => 0,
                            'to' => 4,
                            'value' => $data['main()==>A']['wt'] / $data['main()']['wt'],
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu']
                        ],
                        [
                            'from' => 0,
                            'to' => 5,
                            'value' => $data['main()==>B']['wt'] / $data['main()']['wt'],
                            'ct' => $data['main()==>B']['ct'],
                            'wt' => $data['main()==>B']['wt'],
                            'cpu' => $data['main()==>B']['cpu'],
                            'mu' => $data['main()==>B']['mu'],
                            'pmu' => $data['main()==>B']['pmu']
                        ],
                        [
                            'from' => 4,
                            'to' => 6,
                            'value' => $data['A==>C']['wt'] / $data['main()']['wt'],
                            'ct' => $data['A==>C']['ct'],
                            'wt' => $data['A==>C']['wt'],
                            'cpu' => $data['A==>C']['cpu'],
                            'mu' => $data['A==>C']['mu'],
                            'pmu' => $data['A==>C']['pmu']
                        ],
                        [
                            'from' => 5,
                            'to' => 6,
                            'value' => $data['B==>C']['wt'] / $data['main()']['wt'],
                            'ct' => $data['B==>C']['ct'],
                            'wt' => $data['B==>C']['wt'],
                            'cpu' => $data['B==>C']['cpu'],
                            'mu' => $data['B==>C']['mu'],
                            'pmu' => $data['B==>C']['pmu']
                        ],
                    ]
                ])
        ;
    }

    /**
     *                +--> B
     *                |
     * main() --> A --+
     *                |
     *                +--> C
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
                $profile = $this->newTestedInstance($data),
                $name = uniqid()
            )
            ->then
                ->array($this->testedInstance->dump($name))->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $name,
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => '31123e052ef54c47b8253a371f89bacf',
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
                            'wtip' => $data['main()']['wt'] / $data['main()']['wt'],
                            'wtep' => ($data['main()']['wt'] - $data['main()==>A']['wt']) / $data['main()']['wt'],
                            'cpup' => $data['main()']['cpu'] / $data['main()']['cpu'],
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
                            'wtip' => $data['main()==>A']['wt'] / $data['main()']['wt'],
                            'wtep' => ($data['main()==>A']['wt'] - $data['A==>B']['wt'] - $data['A==>C']['wt']) / $data['main()']['wt'],
                            'cpup' => $data['main()==>A']['cpu'] / $data['main()']['cpu'],
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
                            'wtip' => $data['A==>B']['wt'] / $data['main()']['wt'],
                            'wtep' => $data['A==>B']['wt'] / $data['main()']['wt'],
                            'cpup' => $data['A==>B']['cpu'] / $data['main()']['cpu'],
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
                            'wtip' => $data['A==>C']['wt'] / $data['main()']['wt'],
                            'wtep' => $data['A==>C']['wt'] / $data['main()']['wt'],
                            'cpup' => $data['A==>C']['cpu'] / $data['main()']['cpu'],
                        ],
                    ],
                    'calls' => [
                        [
                            'from' => 0,
                            'to' => 4,
                            'value' => $data['main()==>A']['wt'] / $data['main()']['wt'],
                            'ct' => $data['main()==>A']['ct'],
                            'wt' => $data['main()==>A']['wt'],
                            'cpu' => $data['main()==>A']['cpu'],
                            'mu' => $data['main()==>A']['mu'],
                            'pmu' => $data['main()==>A']['pmu']
                        ],
                        [
                            'from' => 4,
                            'to' => 5,
                            'value' => $data['A==>B']['wt'] / $data['main()']['wt'],
                            'ct' => $data['A==>B']['ct'],
                            'wt' => $data['A==>B']['wt'],
                            'cpu' => $data['A==>B']['cpu'],
                            'mu' => $data['A==>B']['mu'],
                            'pmu' => $data['A==>B']['pmu']
                        ],
                        [
                            'from' => 4,
                            'to' => 6,
                            'value' => $data['A==>C']['wt'] / $data['main()']['wt'],
                            'ct' => $data['A==>C']['ct'],
                            'wt' => $data['A==>C']['wt'],
                            'cpu' => $data['A==>C']['cpu'],
                            'mu' => $data['A==>C']['mu'],
                            'pmu' => $data['A==>C']['pmu']
                        ],
                    ]
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
                    'hash' => 'b49a6991f0854831529db58596424333',
                    'functions' => [],
                    'calls' => []
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
                    'name' => PHP_BINARY . ' ' . implode(' ', $_SERVER['argv']),
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => 'b49a6991f0854831529db58596424333',
                    'functions' => [],
                    'calls' => []
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
                    'name' => $_SERVER['REQUEST_METHOD'] . ' http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => 'b49a6991f0854831529db58596424333',
                    'functions' => [],
                    'calls' => []
                ])
            ->if($_SERVER['HTTPS'] = 'yes')
            ->then
                ->array($this->testedInstance->dump())->isEqualTo([
                    'version' => \jubianchi\ProfIt\PROFIT_PROFILE_VERSION,
                    'name' => $_SERVER['REQUEST_METHOD'] . ' https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    'date' => (new \DateTimeImmutable($this->date))->format(DATE_ISO8601),
                    'hash' => 'b49a6991f0854831529db58596424333',
                    'functions' => [],
                    'calls' => []
                ])
        ;
    }
}
