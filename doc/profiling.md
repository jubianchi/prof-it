# prof-it - Profiling

In the following section, we will profile the following code:

```php
<?php

function one(int $size = null): array
{
    return two($size ?: 100);
}

function two(int $size): array
{
    return array_filter(range(0, $size), function ($i) { return $i % 2; });
}
```

## Run your first profile

To run your first profile you will need an instance of `jubianchi\ProfIt\Profiler`. With this class you will be able to
start and stop the profiling session: 

```php
<?php

$profiler = new \jubianchi\ProfIt\Profiler();
$profiler->start();

one(1000);

$profile = $profiler->stop();
```

As you can see in the above example, the `start` method will ask the profiler to start collect data. The `stop` method 
will then ask the profiler to stop profiling and will return the actual profile data as a `jubianchi/ProfIt/Profile`
instance. 

You will now have to export the profile data to be able to open it in the client application:

```php
<?php

$profile = $profiler->stop();
$profile->export(__DIR__);
```

The `export` method will write the profile data to a file in the provided directory.

## Open your first profile
