# APC cache Kohana core override

This dir contains Kohana core class which overrides default file caching and
use APC cache.

## Requirements

APC extension

## Usage

To make APC storage cache copy Kohana.php from this dir to Your
application/classes dir. This will override default static cache method used
by built-in Kohana core class. This storage can be used to cache ORM / database
queries. To back to default file caching just delete kohana.php from
application/classes dir.
