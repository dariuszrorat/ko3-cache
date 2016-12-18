<?php

defined('SYSPATH') or die('No direct script access.');

class Kohana extends Kohana_Core
{
        /**
         * Using MAX CACHE LIFE to ensure that cache works properly.
         * WARNING! This const must be greather than kohana cache_life
         * or lifetime used in get cached values.
         */
        const MAX_CACHE_LIFE = 2592000;

	/**
	 * Provides simple caching for strings and arrays:
	 *
	 *     // Set the "foo" cache
	 *     Kohana::cache('foo', 'hello, world');
	 *
	 *     // Get the "foo" cache
	 *     $foo = Kohana::cache('foo');
	 *
	 * All caches are stored to cache, generated with [var_export][ref-var].
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
		if ($lifetime === NULL)
		{
			// Use the default lifetime
			$lifetime = Kohana::$cache_life;
		}

                $cache = Cache::instance();

		if ($data === NULL)
		{
                    try
                    {
                        $cache_data = $cache->get($name);
                        if ($cache_data)
                        {
                            $data = $cache_data['data'];
                            $created = $cache_data['created'];

                            if ((time() - $created) < $lifetime)
                            {
                                return $data;
                            }
                            else
                            {
                                $cache->delete($name);
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
                    // Using 'created' key to emulate standard Kohana filemtime
                    // used on default file caching
                    $cache_data = array(
                            'data'        => $data,
                            'created'     => time()
                            );
                    // Using MAX_CACHE_LIFE to ensure that cache works properly
                    $cache->set($name, $cache_data, Kohana::MAX_CACHE_LIFE);
                    return true;

		}
		catch (Exception $e)
		{
		    // Failed to write cache
		    return FALSE;
		}
	}

}
