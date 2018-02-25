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
        $this->data = array_merge(
            [
                'php' => null,
                'osf' => null,
                'os' => null,
                'sapi' => null,
                'extensions' => [
                    'xdebug'=> null,
                    'opcache'=> null,
                ],
                'profile' => [],
            ],
            $data
        );

        $this->buildGraph();
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
                    'value' => round($edge->getAttribute('wt') / $this->graph->getAttribute('wt'), 3),
                ],
                $edge->getAttributeBag()->getAttributes()
            );
        }

        $profile = [
            'php' => $this->data['php'],
            'osf' => $this->data['osf'],
            'os' => $this->data['os'],
            'sapi' => $this->data['sapi'],
            'extensions' => [
                'xdebug'=> $this->data['extensions']['xdebug'],
                'opcache'=> $this->data['extensions']['opcache'],
            ],
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

    public function export(string $path, string $name = null)
    {
        $data = $this->dump($name);

        file_put_contents($path.DIRECTORY_SEPARATOR.$data['hash'].'.json', json_encode($data));
    }

    private function buildGraph()
    {
        foreach ($this->data['profile'] as $call => $stats) {
            $parts = explode('==>', $call);

            if (2 === count($parts)) {
                list($parent, $child) = $parts;

                $parent = self::addFunction($this->graph, $parent);
                $child = self::addFunction($this->graph, $child);

                self::addCall($parent, $child, $stats);
            } else {
                $parent = self::addFunction($this->graph, 'init');
                $child = self::addFunction($this->graph, $parts[0]);

                $this->graph->setAttribute('wt', $stats['wt']);
                $this->graph->setAttribute('cpu', $stats['cpu']);

                self::addCall($parent, $child, $stats);
            }
        }

        foreach ($this->graph->getVertices() as $vertex) {
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

            $vertex->setAttribute('wtip', round($vertex->getAttribute('wt') / $this->graph->getAttribute('wt'), 3));
            $vertex->setAttribute('wtep', round($vertex->getAttribute('wte') / $this->graph->getAttribute('wt'), 3));
            $vertex->setAttribute('cpup', round($vertex->getAttribute('cpu') / $this->graph->getAttribute('cpu'), 3));
        }

        if ($this->graph->hasVertex('init')) {
            $this->graph->getVertex('init')->destroy();
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
