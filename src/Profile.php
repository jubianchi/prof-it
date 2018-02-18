<?php

declare(strict_types=1);

namespace jubianchi\ProfIt;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

class Profile
{
    /**
     * @var Graph
     */
    private $graph;

    public function __construct(array $data, Graph $graph = null)
    {
        $this->graph = $graph ?: new Graph();

        self::buildGraph($this->graph, $data);
    }

    public function dump(string $name = null): array
    {
        $vertices = [];
        $edges = [];

        foreach ($this->graph->getVertices() as $k => $vertex) {
            $vertices[$vertex->getId()] = array_merge(
                [
                    'id' => count($vertices),
                    'label' => $vertex->getId(),
                ],
                $vertex->getAttributeBag()->getAttributes()
            );
        }

        foreach ($this->graph->getEdges() as $edge) {
            $edges[] = array_merge(
                [
                    'from' => $vertices[$edge->getVertexStart()->getId()]['id'],
                    'to' => $vertices[$edge->getVertexEnd()->getId()]['id'],
                    'value' => $edge->getAttribute('wt') / $this->graph->getAttribute('wt'),
                ],
                $edge->getAttributeBag()->getAttributes()
            );
        }

        $profile = [
            'functions' => array_values($vertices),
            'calls' => $edges,
        ];

        if (null === $name) {
            if (isset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'])) {
                $secure = $_SERVER['HTTPS'] ?? '' !== '';

                $name = sprintf(
                    '%s http%s://%s%s',
                    $_SERVER['REQUEST_METHOD'],
                    $secure ? 's' : '',
                    $_SERVER['HTTP_HOST'],
                    $_SERVER['REQUEST_URI']
                );
            } elseif (isset($_SERVER['argv'])) {
                $name = implode(' ', array_merge([PHP_BINARY], $_SERVER['argv']));
            } else {
                $name = 'profile_'.time();
            }
        }

        return [
            'version' => PROFIT_PROFILE_VERSION,
            'name' => $name,
            'date' => (new \DateTimeImmutable(date(DATE_ISO8601)))->format(DATE_ISO8601),
            'hash' => md5(json_encode($profile)),
        ] + $profile;
    }

    public function export(string $path, string $name = null): void
    {
        $data = $this->dump($name);

        file_put_contents($path.DIRECTORY_SEPARATOR.$data['hash'].'.json', json_encode($data));
    }

    private static function buildGraph(Graph $graph, array $data): void
    {
        foreach ($data as $call => $stats) {
            $parts = explode('==>', $call);

            if (2 === count($parts)) {
                [$parent, $child] = $parts;

                $parent = self::addFunction($graph, $parent);
                $child = self::addFunction($graph, $child);

                self::addCall($parent, $child, $stats);
            } else {
                $parent = self::addFunction($graph, 'init');
                $child = self::addFunction($graph, $parts[0]);

                $graph->setAttribute('wt', $stats['wt']);
                $graph->setAttribute('cpu', $stats['cpu']);

                self::addCall($parent, $child, $stats);
            }
        }

        foreach ($graph->getVertices() as $vertex) {
            $edgesIn = $vertex->getEdgesIn();

            foreach ($edgesIn as $edge) {
                $vertex->setAttribute('ct', $vertex->getAttribute('ct', 0) + $edge->getAttribute('ct', 0));
                $vertex->setAttribute('wt', $vertex->getAttribute('wt', 0) + $edge->getAttribute('wt', 0));
                $vertex->setAttribute('wte', $vertex->getAttribute('wte', 0) + $edge->getAttribute('wt', 0));
                $vertex->setAttribute('cpu', $vertex->getAttribute('cpu', 0) + $edge->getAttribute('cpu', 0));
                $vertex->setAttribute('mu', $vertex->getAttribute('mu', 0) + $edge->getAttribute('mu', 0));
                $vertex->setAttribute('pmu', $vertex->getAttribute('pmu', 0) + $edge->getAttribute('pmu', 0));
            }

            $edgesOut = $vertex->getEdgesOut();

            foreach ($edgesOut as $edge) {
                $vertex->setAttribute('wte', $vertex->getAttribute('wte') - $edge->getAttribute('wt', 0));
            }

            $vertex->setAttribute('wtip', $vertex->getAttribute('wt') / $graph->getAttribute('wt'));
            $vertex->setAttribute('wtep', $vertex->getAttribute('wte') / $graph->getAttribute('wt'));
            $vertex->setAttribute('cpup', $vertex->getAttribute('cpu') / $graph->getAttribute('cpu'));
        }

        if ($graph->hasVertex('init')) {
            $graph->getVertex('init')->destroy();
        }
    }

    private static function addFunction(Graph $graph, string $function): Vertex
    {
        $vertex = null;

        if (true === $graph->hasVertex($function)) {
            $vertex = $graph->getVertex($function);
        }

        if (null === $vertex) {
            $vertex = $graph->createVertex($function);
            $id = $graph->getVertices()->count() + 1;

            if ('main()' === $function) {
                $id = 0;
            }

            $vertex->setAttribute('id', $id);
        }

        return $vertex;
    }

    private static function addCall(Vertex $from, Vertex $to, array $stats): Directed
    {
        $edge = null;

        if ($from->hasEdgeTo($to)) {
            $edge = $from->getEdgesTo($to)[0];
        }

        if (null === $edge) {
            $edge = $from->createEdgeTo($to);
        }

        $edge->setAttribute('ct', $edge->getAttribute('ct', 0) + $stats['ct'] ?? 0);
        $edge->setAttribute('wt', $edge->getAttribute('wt', 0) + $stats['wt'] ?? 0);
        $edge->setAttribute('cpu', $edge->getAttribute('cpu', 0) + $stats['cpu'] ?? 0);
        $edge->setAttribute('mu', $edge->getAttribute('mu', 0) + $stats['mu'] ?? 0);
        $edge->setAttribute('pmu', $edge->getAttribute('pmu', 0) + $stats['pmu'] ?? 0);

        return $edge;
    }
}
