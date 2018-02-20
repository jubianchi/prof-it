<?php

require_once __DIR__ . '/../vendor/autoload.php';

function one(int $size = null): array
{
    return two($size ?: 100);
}

function two(int $size): array
{
    return array_filter(range(0, $size), function ($i) { return $i % 2; });
}

$profiler = new \jubianchi\ProfIt\Profiler();
$profiler->start();

one(1000);

$profile = $profiler->stop();
$profile->export(__DIR__);