<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * [Kohana Cache](api/Kohana_Cache) Redis driver. Provides a Redis based
 * driver for the Kohana Cache library.
 *
 * ### Configuration example
 *
 * Below is an example of a _redis_ server configuration.
 *
 *     return array(
 *          'redis'   => array(                          // Redis driver group
 *                  'driver'         => 'redis',         // using Redis driver
 *                  'host'           => '127.0.0.1',     // Redis host
 *                  'port'           => 6379,            // Redis port
 *                  'database'       => 15               // Redis database
 *           ),
 *     )
 *
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to all types of cache driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 * host           | __YES__   | (_string_) The host to use for this cache instance
 * port           | __YES__   | (_integer_) The port to use for this cache instance
 * database       | __YES__   | (_integer_) The database to use for this cache instance
 *
 * ### System requirements
 *
 * *  Kohana 3.0.x
 * *  PHP 5.3 or greater
 *
 * @package    Kohana/Cache
 * @category   Base
 * @author     Dariusz Rorat
 * @copyright  (c) 2015 Dariusz Rorat

 */

include Kohana::find_file('vendor/predis', 'autoload');

class Kohana_Cache_Redis extends Cache_Nosql
{

    /**
     * Constructs the redis cache driver. This method cannot be invoked externally. The redis cache driver must
     * be instantiated using the `Cache::instance()` method.
     *
     * @param   array  $config  config
     * @throws  Cache_Exception
     */
    protected function __construct(array $config)
    {
        // Setup parent
        parent::__construct($config);

        $single_server = array(
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => $config['database']
        );

        $this->_client = new Predis\Client($single_server);
    }

}
