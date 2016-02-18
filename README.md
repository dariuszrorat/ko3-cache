# Nosql cache drivers for Kohana Framework

This module can be used to store Your data in the Redis and SSDB storage. The core dir
contains kohana.php with default file cache override and use Redis storage instead.
You can use this file to cache ORM / database queries. Just extract and copy
kohana.php from core dir and place this file in Your application/classes dir.

## Requirements

The redis module requires predis library:
https://github.com/nrk/predis

Install this library on:

application/vendor

autoload.php must be in:

application/vendor/predis

No need to install Redis PHP extension module.

The SSDB driver uses SSDB PHP library from ssdb.io
and must be installed in vendor/SSDB

The MongoDB cache uses MongoClient. Requires php_mongo extension module.

## Config

cache.php

```php
return array
(
        'redis'   => array(
                'driver'             => 'redis',
                'host'               => '127.0.0.1',
                'port'               => 6379,
                'database'           => 15,
		'default_expire'     => 3600,
            ),
        'ssdb'   => array(
                'driver'             => 'SSDB',
                'host'               => '127.0.0.1',
                'port'               => 8888,
                'timeout'            => 2000,
		'default_expire'     => 3600,
            ),
        'mongo'   => array(
                'driver'             => 'mongo',
                'host'               => '127.0.0.1',
                'port'               => 27017,
                'database'           => 'kohana',
                'collection'         => 'caches',
		'default_expire'     => 3600,
            ),
);

```

