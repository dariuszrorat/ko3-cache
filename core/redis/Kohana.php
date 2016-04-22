<?php defined('SYSPATH') or die('No direct script access.');

class Kohana extends Kohana_Core
{

	/**
         * Default Redis host
	 * @var  string
	 */
	public static $redis_host = '127.0.0.1';

	/**
         * Default Redis port
	 * @var  integer
	 */
	public static $redis_port = 6379;

	/**
         * Default Redis database
	 * @var  integer
	 */
	public static $redis_database = 15;

	/**
	 * Initializes the environment:
	 *
	 * The following settings can be set:
	 *
	 * Type      | Setting        | Description          | Default Value
	 * ----------|----------------|----------------------|---------------
	 * `string`  | redis_host     | The redis host       | `"127.0.0.1"`
	 * `integer` | redis_port     | The redis port       | `6379`
	 * `integer` | redis_database | The redis database   | `15`
	 *
	 * @throws  Kohana_Exception
	 * @param   array   Array of settings.  See above.
	 * @return  void
	 */

        public static function init(array $settings = NULL)
        {
            parent::init($settings);

            if (isset($settings['redis_host']))
	    {
	        Kohana::$redis_host = (string) $settings['redis_host'];
	    }

            if (isset($settings['redis_port']))
	    {
	        Kohana::$redis_port = (integer) $settings['redis_port'];
	    }

            if (isset($settings['redis_database']))
	    {
	        Kohana::$redis_database = (integer) $settings['redis_database'];
	    }
        }

	/**
	 * Provides simple redis-based caching for strings and arrays:
	 *
	 *     // Set the "foo" cache
	 *     Kohana::cache('foo', 'hello, world');
	 *
	 *     // Get the "foo" cache
	 *     $foo = Kohana::cache('foo');
	 *
	 * All caches are stored to Redis server, generated with [var_export][ref-var].
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
                require_once Kohana::find_file('vendor/predis', 'autoload');

		if ($lifetime === NULL)
		{
			// Use the default lifetime
			$lifetime = Kohana::$cache_life;
		}

                $single_server = array(
                    'host'     => Kohana::$redis_host,
                    'port'     => Kohana::$redis_port,
                    'database' => Kohana::$redis_database
                );

                $client = new Predis\Client($single_server);


		if ($data === NULL)
		{
                    try
                    {
                        if (($serialized = $client->get($name)) !== NULL)
                        {
                            $clientdata = unserialize($serialized);
                            $created = $clientdata['created'];
                            $diff = time() - $created;
                            if ((time() - $created) < $lifetime)
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
                            'created'     => time(),
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
