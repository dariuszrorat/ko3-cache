<?php defined('SYSPATH') or die('No direct script access.');

class Kohana extends Kohana_Core
{

	/**
         * Default SSDB host
	 * @var  string
	 */
	public static $ssdb_host = '127.0.0.1';

	/**
         * Default SSDB port
	 * @var  integer
	 */
	public static $ssdb_port = 8888;

	/**
         * Default SSDB timeout
	 * @var  integer
	 */
	public static $ssdb_timeout = 2000;

	/**
	 * Initializes the environment:
	 *
	 * The following settings can be set:
	 *
	 * Type      | Setting        | Description          | Default Value
	 * ----------|----------------|----------------------|---------------
	 * `string`  | ssdb_host      | The ssdb host        | `"127.0.0.1"`
	 * `integer` | ssdb_port      | The ssdb port        | `8888`
	 * `integer` | ssdb_timeout   | The ssdb timeout     | `2000`
	 *
	 * @throws  Kohana_Exception
	 * @param   array   Array of settings.  See above.
	 * @return  void
	 */

        public static function init(array $settings = NULL)
        {
            parent::init($settings);

            if (isset($settings['ssdb_host']))
	    {
	        Kohana::$ssdb_host = (string) $settings['ssdb_host'];
	    }

            if (isset($settings['ssdb_port']))
	    {
	        Kohana::$ssdb_port = (integer) $settings['ssdb_port'];
	    }

            if (isset($settings['ssdb_timeout']))
	    {
	        Kohana::$ssdb_timeout = (integer) $settings['ssdb_timeout'];
	    }
        }

	/**
	 * Provides simple ssdb-based caching for strings and arrays:
	 *
	 *     // Set the "foo" cache
	 *     Kohana::cache('foo', 'hello, world');
	 *
	 *     // Get the "foo" cache
	 *     $foo = Kohana::cache('foo');
	 *
	 * All caches are stored to SSDB server, generated with [var_export][ref-var].
	 * Caching objects may not work as expected. Storing references or an
	 * object or array that has recursion will cause an E_FATAL.
	 *
	 * The cache directory and default cache lifetime is set by [Kohana::init]
	 *
	 * [ref-var]: http://php.net/var_export
	 *
	 * @throws  Kohana_Exception
	 * @param   string   name of the cache
	 * @param   mixed    data to cache
	 * @param   integer  number of seconds the cache is valid for
	 * @return  mixed    for getting
	 * @return  boolean  for setting
	 */
	public static function cache($name, $data = NULL, $lifetime = NULL)
	{
                require_once Kohana::find_file('vendor/SSDB', 'SSDB');

		if ($lifetime === NULL)
		{
			// Use the default lifetime
			$lifetime = Kohana::$cache_life;
		}

                $client = new new SimpleSSDB(Kohana::$ssdb_host, Kohana::$ssdb_port, Kohana::$ssdb_timeout);


		if ($data === NULL)
		{
                    try
                    {
                        if (($serialized = $client->get($name)) !== NULL)
                        {
                            $clientdata = unserialize($serialized);
                            $created = $clientdata['created'];
                            $lifetime = $clientdata['lifetime'];
                            $now = date('Y-m-d H:i:s');
                            $date_now = new DateTime($now);
                            $date_created = new DateTime($created);
                            $diff = $date_now->getTimestamp() - $date_created->getTimestamp();
                            if ($diff <= $lifetime)
                            {
                                return $clientdata['data'];
                            }
                            else
                            {
                                $client->del($name);
                                return NULL;
                            }
                        }
                        else
                        {
                            // Cache not found
                            return NULL;
                        }
                    }
                    catch (Exception $e)
                    {
                        //Failed to read cache
                        return NULL;
                    }

		}

		try
		{
		    // Write the cache
                    $serverdata = array(
                            'data'        => $data,
                            'created'     => date('Y-m-d H:i:s'),
                            'lifetime'    => $lifetime
                            );
                    $client->set($name, serialize($serverdata));
                    return true;

		}
		catch (Exception $e)
		{
		    // Failed to write cache
		    return FALSE;
		}
	}

}
