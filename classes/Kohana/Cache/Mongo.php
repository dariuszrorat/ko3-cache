<?php

defined('SYSPATH') or die('No direct script access.');
/**
 * [Kohana Cache](api/Kohana_Cache) MongoDB driver. Provides a MongoDB based
 * driver for the Kohana Cache library.
 *
 * ### Configuration example
 *
 * Below is an example of a _redis_ server configuration.
 *
 *     return array(
 *          'mongo'   => array(                          // Mongo driver group
 *                  'driver'         => 'mongo',         // using Mongo driver
 *                  'host'           => 'localhost',     // Mongo host
 *                  'port'           => 27017,           // Mongo port
 *                  'database'       => 'kohana',        // Mongo database
 *                  'collection'     => 'caches'         // Mongo collection
 *                  'default_expire'     => 3600,
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
 * database       | __YES__   | (_string_) The database to use for this cache instance
 * collection     | __YES__   | (_string_) The collection to use for this cache instance
 * default_expire | __NO__   | (_integer_) Default expiration time in s

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

class Kohana_Cache_Mongo extends Cache implements Cache_GarbageCollect
{

    protected $_host;
    protected $_port;
    protected $_database;
    protected $_collection;

    protected $_client;
    protected $_selected_collection;
    /**
     * Constructs the mongo cache driver. This method cannot be invoked externally.
     * The mongo cache driver must be instantiated using the `Cache::instance()` method.
     *
     * @param   array  $config  config
     * @throws  Cache_Exception
     */
    protected function __construct(array $config)
    {
        // Setup parent
        parent::__construct($config);

        $this->_host = $config['host'];
        $this->_port = $config['port'];
        $this->_database = $config['database'];
        $this->_collection = $config['collection'];

        $dsn = 'mongodb://' . $this->_host . ':' . $this->_port;
        try
        {
            $this->_client = new MongoClient($dsn);
            $this->_selected_collection = $this->_client->selectCollection($this->_database, $this->_collection);
        }
        catch (Exception $e)
        {
            throw new Cache_Exception('Failed to connect to MongoDB server with the following error : :error', array(':error' => $e->getMessage()));
        }

    }

    /**
     * Retrieve a cached value entry by id.
     *
     *     // Retrieve cache entry from foo group
     *     $data = Cache::instance('foo')->get('foo');
     *
     *     // Retrieve cache entry from redis group and return 'bar' if miss
     *     $data = Cache::instance('foo')->get('foo', 'bar');
     *
     * @param   string   $id       id of cache to entry
     * @param   string   $default  default value to return if cache miss
     * @return  mixed
     * @throws  Cache_Exception
     */
    public function get($id, $default = NULL)
    {
        $where = array('id' => $id);

        try
        {
            $result = $this->_selected_collection->findOne($where);
            if ($result !== NULL)
            {
                $data = $result['data'];
                $expires = $result['expires'];
                $now = time();
                if ($now <= $expires)
                {
                    return $data;
                } else
                {
                    return $default;
                }
            } else
            {
                return $default;
            }

        }
        catch (Exception $e)
        {
            throw new Cache_Exception('Failed to retrieve MongoDB data with the following error : :error', array(':error' => $e->getMessage()));
        }
    }

    /**
     * Set a value to cache with id and lifetime
     *
     *     $data = 'bar';
     *
     *     // Set 'bar' to 'foo' in redis group, using default expiry
     *     Cache::instance('foo')->set('foo', $data);
     *
     *     // Set 'bar' to 'foo' in redis group for 30 seconds
     *     Cache::instance('foo')->set('foo', $data, 30);
     *
     * @param   string   $id        id of cache entry
     * @param   string   $data      data to set to cache
     * @param   integer  $lifetime  lifetime in seconds
     * @return  boolean
     * @throws  Cache_Exception
     */
    public function set($id, $data, $lifetime = NULL)
    {
            $life_time = ($lifetime !== null) ? $lifetime : Cache::DEFAULT_EXPIRE;

            try
            {
                $where = array('id' => $id);
                $result = $this->_selected_collection->findOne($where);
                $now = time();
                if ($result === NULL)
                {

                    $data = array(
                          'id'   => $id,
                          'data' => $data,
                          'expires'  => ($now + $life_time)
                    );
                    $this->_selected_collection->insert($data);
                }
                else
                {
                    $data = array(
                          'data' => $data,
                          'expires' => ($now + $life_time)
                    );
                    $this->_selected_collection->update($where, array('$set' => $data), array('multiple' => false));
                }
                return true;
            }
            catch (Exception $e)
            {
                throw new Cache_Exception('Failed to insert MongoDB data with the following error : :error', array(':error' => $e->getMessage()));
            }

        return false;
    }

    /**
     * Delete a cache entry based on id
     *
     *     // Delete 'foo' entry from the redis group
     *     Cache::instance('foo')->delete('foo');
     *
     * @param   string   $id  id to remove from cache
     * @return  boolean
     * @throws  Cache_Exception
     */
    public function delete($id)
    {
        try
        {
            $where = array('id' => $id);
            $this->_selected_collection->remove($where);
            return true;
        } catch (Exception $e)
        {
            throw new Cache_Exception('Failed to delete MongoDB data with the following error : :error', array(':error' => $e->getMessage()));
        }
        return false;
    }

    /**
     * Delete all cache entries.
     *
     * Beware of using this method when
     * using shared memory cache systems, as it will wipe every
     * entry within the system for all clients.
     *
     *     // Delete all cache entries in the redis group
     *     Cache::instance('foo')->delete_all();
     *
     * @return  boolean
     * @throws  Cache_Exception
     */
    public function delete_all()
    {
        try
        {
            $this->_selected_collection->remove();
            return true;
        } catch (Exception $e)
        {
            throw new Cache_Exception('Failed to delete MongoDB data with the following error : :error', array(':error' => $e->getMessage()));
        }
        return false;
    }

    /**
     * Delete all expired caches.
     *
     *     Cache::instance('foo')->garbage_collect();
     *
     * @return  void
     * @throws  Cache_Exception
     */

    public function garbage_collect()
    {
        try
        {
            $now = time();
            $where = array('expires' => array('$lt' => $now));
            $this->_selected_collection->remove($where);
            return;
        } catch (Exception $e)
        {
            throw new Cache_Exception('Failed to delete MongoDB data with the following error : :error', array(':error' => $e->getMessage()));
        }
    }
}
