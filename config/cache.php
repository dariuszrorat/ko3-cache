<?php defined('SYSPATH') or die('No direct script access.');

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


);
