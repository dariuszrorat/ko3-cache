<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * [Kohana Cache](api/Kohana_Cache) SSDB driver. Provides a SSDB based
 * driver for the Kohana Cache library.
 *
 * ### Configuration example
 *
 * Below is an example of a _SSDB_ server configuration.
 *
 *     return array(
 *          'ssdb'   => array(                          // SSDB driver group
 *                  'driver'         => 'SSDB',          // using SSDB driver
 *                  'host'           => '127.0.0.1',     // SSDB host
 *                  'port'           => 8888,            // SSDB port
 *                  'timeout'       =>  2000               // SSDB timeout
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
 * host           | __YES__  | (_string_) The host to use for this cache instance
 * port           | __YES__  | (_integer_) The port to use for this cache instance
 * timeout        | __NO__   | (_integer_) The connection timeout
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

class Kohana_Cache_SSDB extends Cache_Nosql
{

    /**
     * Constructs the SSDB cache driver. This method cannot be invoked externally. The SSDB cache driver must
     * be instantiated using the `Cache::instance()` method.
     *
     * @param   array  $config  config
     * @throws  Cache_Exception
     */
    protected function __construct(array $config)
    {
        include Kohana::find_file('vendor/SSDB', 'SSDB');
        // Setup parent
        parent::__construct($config);

        $host = $config['host'];
        $port = $config['port'];
        $timeout = $config['timeout'];

        $this->_client = new SimpleSSDB($host, $port, $timeout);
    }

}
