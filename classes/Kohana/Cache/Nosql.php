<?php

defined('SYSPATH') or die('No direct script access.');

class Kohana_Cache_Nosql extends Cache
{

    /**
     *  Nosql client
     */
    protected $_client = null;

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
     */
    public function get($id, $default = NULL)
    {
        try
        {
            if (($serialized = $this->_client->get($id)) !== NULL)
            {
                $clientdata = unserialize($serialized);
                $created = $clientdata['created'];
                $lifetime = $clientdata['lifetime'];
                $diff = time() - $created;
                if ($diff <= $lifetime)
                {
                    return $clientdata['data'];
                } else
                {
                    return $default;
                }
            } else
            {
                return $default;
            }
        } catch (ErrorException $e)
        {
            // Handle ErrorException caused by failed unserialization
            if ($e->getCode() === E_NOTICE)
            {
                throw new Cache_Exception(__METHOD__ . ' failed to unserialize cached object with message : ' . $e->getMessage());
            }

            // Otherwise throw the exception
            throw $e;
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
        try
        {
            $life_time = ($lifetime !== null) ? $lifetime : Cache::DEFAULT_EXPIRE;
            $serverdata = array(
                'data' => $data,
                'created' => time(),
                'lifetime' => $life_time
            );
            $this->_client->set($id, serialize($serverdata));
            return true;
        } catch (ErrorException $e)
        {
            // If serialize through an error exception
            if ($e->getCode() === E_NOTICE)
            {
                // Throw a caching error
                throw new Cache_Exception(__METHOD__ . ' failed to serialize data for caching with message : ' . $e->getMessage());
            }

            // Else rethrow the error exception
            throw $e;
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
            $this->_client->del($id);
            return true;
        } catch (ErrorException $e)
        {
            // If serialize through an error exception
            if ($e->getCode() === E_NOTICE)
            {
                // Throw a caching error
                throw new Cache_Exception(__METHOD__ . ' failed to delete data for caching with message : ' . $e->getMessage());
            }

            // Else rethrow the error exception
            throw $e;
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
     */
    public function delete_all()
    {
        return false;
    }

}
