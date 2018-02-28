<?php

declare(strict_types=1);

namespace jubianchi\ProfIt\Profiler;

class Filter
{
    /**
     * @var string[]
     */
    private $excludedNamespaces = [];

    /**
     * @var string[]
     */
    private $includedNamespaces = [];

    public function withExcludedNamespace(string $namespace): self
    {
        if (true === in_array($namespace, $this->excludedNamespaces)) {
            return $this;
        }

        $filter = clone $this;

        $filter->excludedNamespaces[] = $namespace;

        return $filter;
    }

    public function withIncludedNamespace(string $namespace): self
    {
        if (in_array($namespace, $this->includedNamespaces)) {
            return $this;
        }

        $filter = clone $this;

        $filter->includedNamespaces[] = $namespace;

        return $filter;
    }

    public function filter(array $profile)
    {
        return array_filter(
            $profile,
            function ($call) {
                $parts = explode('==>', $call);

                if (1 === count($parts)) {
                    return true;
                }

                list($caller, $callee) = $parts;

                $callerExcluded = $this->isExcluded($caller) && !$this->isIncluded($caller);
                $calleeExcluded = $this->isExcluded($callee) && !$this->isIncluded($callee);

                if (false === $calleeExcluded && 'main()' === $caller) {
                    return true;
                }

                return !$callerExcluded && !$calleeExcluded;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function isExcluded(string $caller): bool
    {
        return self::isNamespaceIn($this->excludedNamespaces, $caller);
    }

    private function isIncluded(string $caller): bool
    {
        return self::isNamespaceIn($this->includedNamespaces, $caller);
    }

    private static function isNamespaceIn(array $list, string $caller)
    {
        $callerNamespace = self::getNamespace($caller);

        foreach ($list as $namespace) {
            if (0 === strpos($callerNamespace, $namespace)) {
                return true;
            }
        }

        return false;
    }

    private static function getNamespace($caller)
    {
        return implode('\\', array_slice(explode('\\', $caller), 0, -1)) ?: '\\';
    }
}
