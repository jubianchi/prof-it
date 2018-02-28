<?php

declare(strict_types=1);

namespace jubianchi\ProfIt\Tests\Unit\Profiler;

use mageekguy\atoum;

class Filter extends atoum
{
    public function testWithExcludedNamespace()
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->object($this->testedInstance->withExcludedNamespace(uniqid()))
                    ->isNotTestedInstance
                    ->isInstanceOfTestedClass
        ;
    }

    public function testExcludeNamespace()
    {
        $this
            ->given($profile = ['main()==>Foo\Bar\Baz::qux' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('Foo')->filter($profile))
                    ->notHasKey('main()==>Foo\Bar\Baz::qux')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar')->filter($profile))
                    ->notHasKey('main()==>Foo\Bar\Baz::qux')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar\Baz')->filter($profile))
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
        ;
    }

    public function testExcludeRootNamespace()
    {
        $this
            ->given($profile = ['main()==>Foo\Bar\Baz::qux' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('\\')->filter($profile))
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
            ->given($profile = ['main()==>Foo::bar' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('\\')->filter($profile))
                    ->notHasKey('main()==>Foo::bar')
            ->given($profile = ['Foo\Bar\Baz::qux==>substr' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('\\')->filter($profile))
                    ->notHasKey('Foo\Bar\Baz::qux==>substr')
        ;
    }

    public function testNeverExcludeMain()
    {
        $this
            ->given($profile = [
                'main()' => [],
                'main()==>Foo\Bar\Baz::qux' => [],
            ])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('\\')->filter($profile))
                    ->hasKey('main()')
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
        ;
    }

    public function testExcludeCallerNamespace()
    {
        $this
            ->given($profile = ['Foo\Bar\Baz::qux==>Qux\Quxx::corge' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('Foo')->filter($profile))
                    ->notHasKey('Foo\Bar\Baz::qux==>Qux\Quxx::corge')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar')->filter($profile))
                    ->notHasKey('Foo\Bar\Baz::qux==>Qux\Quxx::corge')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar\Baz')->filter($profile))
                    ->hasKey('Foo\Bar\Baz::qux==>Qux\Quxx::corge')
        ;
    }

    public function testExcludeCalleeNamespace()
    {
        $this
            ->given($profile = ['Qux\Quxx::corge==>Foo\Bar\Baz::qux' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('Foo')->filter($profile))
                    ->notHasKey('Qux\Quxx::corge==>Foo\Bar\Baz::qux')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar')->filter($profile))
                    ->notHasKey('Qux\Quxx::corge==>Foo\Bar\Baz::qux')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar\Baz')->filter($profile))
                    ->hasKey('Qux\Quxx::corge==>Foo\Bar\Baz::qux')
        ;
    }

    public function testWithIncludedNamespace()
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->object($this->testedInstance->withExcludedNamespace(uniqid()))
                    ->isNotTestedInstance
                    ->isInstanceOfTestedClass
        ;
    }

    public function testIncludeExcludedNamespace()
    {
        $this
            ->given($profile = ['main()==>Foo\Bar\Baz::qux' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('Foo')->withIncludedNamespace('Foo')->filter($profile))
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar')->withIncludedNamespace('Foo\Bar')->filter($profile))
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo\Bar\Baz')->withIncludedNamespace('Foo\Bar\Baz')->filter($profile))
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
            ->given($profile = ['Foo\Bar\Baz::qux==>substr' => []])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('\\')->withIncludedNamespace('\\')->filter($profile))
                    ->hasKey('Foo\Bar\Baz::qux==>substr')
        ;
    }

    public function testIncludeChildOfExcludedNamespace()
    {
        $this
            ->given($profile = [
                'main()==>Foo\Bar::baz' => [],
                'main()==>Foo\Bar\Baz::qux' => [],
                'main()==>Foo\Qux\Baz::quxx' => [],
            ])
            ->then
                ->array($this->newTestedInstance->withExcludedNamespace('Foo')->withIncludedNamespace('Foo\Bar')->filter($profile))
                    ->notHasKey('main()==>Foo\Bar::baz')
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
                    ->notHasKey('main()==>Foo\Qux\Baz::quxx')
                ->array($this->newTestedInstance->withExcludedNamespace('Foo')->withIncludedNamespace('Foo\Bar')->withIncludedNamespace('Foo\Qux')->filter($profile))
                    ->notHasKey('main()==>Foo\Bar::baz')
                    ->hasKey('main()==>Foo\Bar\Baz::qux')
                    ->hasKey('main()==>Foo\Qux\Baz::quxx')
        ;
    }
}
