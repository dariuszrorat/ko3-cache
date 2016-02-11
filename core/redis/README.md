# Redis cache Kohana core override

This dir contains Kohana core class which overrides default file caching and
use Redis storage database.

## Requirements

This file requires predis library:
https://github.com/nrk/predis

Install this library on:

application/vendor

autoload.php must be in:

application/vendor/predis

This library is internal included included by:

```php
require_once Kohana::find_file('vendor/predis', 'autoload');
```
No need to install Redis PHP extension module.

## Usage

To make Redis storage cache copy kohana.php from this dir to Your
application/classes dir. This will override default static cache method used
by built-in Kohana core class. This storage can be used to cache ORM / database
queries. If the Redis server is unavailable, cache returns NULL. To back to
default file caching just delete kohana.php from application/classes dir.
