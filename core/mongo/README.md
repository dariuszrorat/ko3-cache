# MongoDB cache Kohana core override

This dir contains Kohana core class which overrides default file caching and
use MongoDB storage database.

## Requirements

This cache requires php_mongo extension module.

## Usage

To make MongoDB storage cache copy Kohana.php from this dir to Your
application/classes dir. This will override default static cache method used
by built-in Kohana core class. This storage can be used to cache ORM / database
queries. If the Mongo server is unavailable, cache returns NULL. To back to
default file caching just delete kohana.php from application/classes dir.
