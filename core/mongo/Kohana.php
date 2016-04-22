<?php defined('SYSPATH') or die('No direct script access.');

class Kohana extends Kohana_Core
{

	/**
         * Default Mongo host
	 * @var  string
	 */
	public static $mongo_host = '127.0.0.1';

	/**
         * Default Mongo port
	 * @var  integer
	 */
	public static $mongo_port = 27017;

	/**
         * Default Mongo database
	 * @var  string
	 */
	public static $mongo_database = 'kohana';

	/**
         * Default Mongo collection
	 * @var  string
	 */
	public static $mongo_collection = 'caches';

	/**
	 * Initializes the environment:
	 *
	 * The following settings can be set:
	 *
	 * Type      | Setting          | Description           | Default Value
	 * ----------|----------------- |---------------------- |---------------
	 * `string`  | mongo_host       | The Mongo host        | `"127.0.0.1"`
	 * `integer` | mongo_port       | The Mongo port        | `27017`
	 * `string`  | mongo_database   | The Mongo database    | `kohana`
	 * `string`  | mongo_collection | The Mongo collection  | `caches`
	 *
	 * @throws  Kohana_Exception
	 * @param   array   Array of settings.  See above.
	 * @return  void
	 */

        public static function init(array $settings = NULL)
        {
            parent::init($settings);

            if (isset($settings['mongo_host']))
	    {
	        Kohana::$mongo_host = (string) $settings['mongo_host'];
	    }

            if (isset($settings['mongo_port']))
	    {
	        Kohana::$mongo_port = (integer) $settings['mongo_port'];
	    }

            if (isset($settings['mongo_database']))
	    {
	        Kohana::$mongo_database = (integer) $settings['mongo_database'];
	    }

            if (isset($settings['mongo_collection']))
	    {
	        Kohana::$mongo_collection = (integer) $settings['mongo_collection'];
	    }

        }

	/**
	 * Provides simple mongo-based caching for strings and arrays:
	 *
	 *     // Set the "foo" cache
	 *     Kohana::cache('foo', 'hello, world');
	 *
	 *     // Get the "foo" cache
	 *     $foo = Kohana::cache('foo');
	 *
	 * All caches are stored to mongo server, generated with [var_export][ref-var].
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

                $dsn = 'mongodb://' . Kohana::$mongo_host . ':' . Kohana::$mongo_port;
                $client = new MongoClient($dsn);
                $collection = $client->selectCollection(Kohana::$mongo_database, Kohana::$mongo_collection);
                $where = array('name' => $name);

		if ($data === NULL)
		{
                    try
                    {
                        if (($result = $collection->findOne($where)) !== NULL)
                        {
                            $created = $result['created'];
                            if ((time() - $created) < $lifetime)
                            {
                                return $result['data'];
                            }
                            else
                            {
                                $collection->remove($where, array('justOne' => true));
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
                    $result = $collection->findOne($where);
                    $now = time();
                    if ($result === NULL)
                    {
                        $data = array(
                            'name'   => $name,
                            'data' => $data,
                            'created'     => $now
                        );
                        $collection->insert($data);
                    }
                    else
                    {
                        $data = array(
                            'data' => $data,
                            'created' => $now
                        );
                        $collection->update($where, array('$set' => $data), array('multiple' => false));
                    }

                    return true;

		}
		catch (Exception $e)
		{
		    // Failed to write cache
		    return FALSE;
		}
	}

}
