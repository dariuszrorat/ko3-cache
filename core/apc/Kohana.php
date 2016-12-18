<?php

defined('SYSPATH') or die('No direct script access.');

class Kohana extends Kohana_Core
{
     const APC_CACHE_MAX_LIFE = 2592000;

	/**
	 * Provides simple apc-based caching for strings and arrays:
	 *
	 *     // Set the "foo" cache
	 *     Kohana::cache('foo', 'hello, world');
	 *
	 *     // Get the "foo" cache
	 *     $foo = Kohana::cache('foo');
	 *
	 * All caches are stored to APC cache, generated with [var_export][ref-var].
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


		if ($data === NULL)
		{
                    try
                    {
                        $apc_data = apc_fetch($name, $success);
                        if ($success)
                        {
                            $data = $apc_data['data'];
                            $created = $apc_data['created'];

                            if ((time() - $created) < $lifetime)
                            {
                                return $data;
                            }
                            else
                            {
                                apc_delete($name);
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
                    $apc_data = array(
                            'data'        => $data,
                            'created'     => time()
                            );
                    apc_store($name, $apc_data, Kohana::APC_CACHE_MAX_LIFE);
                    return true;

		}
		catch (Exception $e)
		{
		    // Failed to write cache
		    return FALSE;
		}
	}

}
