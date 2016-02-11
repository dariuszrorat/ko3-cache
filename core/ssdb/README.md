# SSDB cache Kohana core override

This dir contains Kohana core class which overrides default file caching and
use SSDB storage database.

## Requirements

This file requires SSDB library:
http://ssdb.io/

Install this library on:

application/vendor/SSDB

## Usage

To make SSDB storage cache copy Kohana.php from this dir to Your
application/classes dir. This will override default static cache method used
by built-in Kohana core class. This storage can be used to cache ORM / database
queries. If the SSDB server is unavailable, cache returns NULL. To back to
default file caching just delete kohana.php from application/classes dir.
