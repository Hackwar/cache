<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache;

use Joomla\Cache\Item\HasExpirationDateInterface;
use Psr\Cache\CacheItemInterface;
use Joomla\Cache\Exception\RuntimeException;
use Joomla\Cache\Item\Item;

/**
 * Memcached cache driver for the Joomla Framework.
 *
 * @since  1.0
 */
class Memcached extends Cache
{
	/**
	 * @var    \Memcached  The memcached driver, initialize to false to avoid having to repeated run isset before using it..
	 * @since  1.0
	 */
	private $driver = false;

	/**
	 * Constructor.
	 *
	 * @param   mixed  $options  An options array, or an object that implements \ArrayAccess
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct($options = array())
	{
		// Parent sets up the caching options and checks their type
		parent::__construct($options);

		// These options must be set to something, so if not set make them false
		if (!isset($this->options['memcache.pool']))
		{
			$this->options['memcache.pool'] = false;
		}

		if (!isset($this->options['memcache.compress']))
		{
			$this->options['memcache.compress'] = false;
		}

		if (!isset($this->options['memcache.servers']))
		{
			$this->options['memcache.servers'] = false;
		}
	}

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return  boolean  The result of the clear operation.
	 *
	 * @since   1.0
	 */
	public function clear()
	{
		$this->connect();

		return $this->driver->flush();
	}

	/**
	 * Method to get a storage entry value from a key.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  CacheItemInterface
	 *
	 * @since   1.0
	 */
	public function getItem($key)
	{
		$this->connect();
		$value = $this->driver->get($key);
		$code = $this->driver->getResultCode();
		$item = new Item($key);

		if ($code === \Memcached::RES_SUCCESS)
		{
			$item->set($value);
		}

		return $item;
	}

	/**
	 * Method to remove a storage entry for a key.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function deleteItem($key)
	{
		$this->connect();

		if ($this->hasItem($key))
		{
			$this->driver->delete($key);

			$rc = $this->driver->getResultCode();

			if ( ($rc != \Memcached::RES_SUCCESS))
			{
				throw new RuntimeException(sprintf('Unable to remove cache entry for %s. Error message `%s`.', $key, $this->driver->getResultMessage()));
			}
		}

		return true;
	}

	/**
	 * Persists a cache item immediately.
	 *
	 * @param   CacheItemInterface  $item  The cache item to save.
	 *
	 * @return  static  The invoked object.
	 */
	public function save(CacheItemInterface $item)
	{
		$this->connect();

		if ($item instanceof HasExpirationDateInterface)
		{
			$ttl = $this->convertItemExpiryToSeconds($item);
		}
		else
		{
			$ttl = 0;
		}

		$this->driver->set($item->getKey(), $item->get(), $ttl);

		return (bool) ($this->driver->getResultCode() == \Memcached::RES_SUCCESS);
	}

	/**
	 * Method to determine whether a storage entry has been set for a key.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function hasItem($key)
	{
		$this->connect();

		$this->driver->get($key);

		return ($this->driver->getResultCode() != \Memcached::RES_NOTFOUND);
	}

	/**
	 * Test to see if the CacheItemPoolInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		/*
		 * GAE and HHVM have both had instances where Memcached the class was defined but no extension was loaded.
		 * If the class is there, we can assume it works.
		 */
		return (class_exists('Memcached'));
	}

	/**
	 * Connect to the Memcached servers if the connection does not already exist.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function connect()
	{
		// We want to only create the driver once.
		if ($this->driver)
		{
			return;
		}

		$pool = $this->options['memcache.pool'];

		if ($pool)
		{
			$this->driver = new \Memcached($pool);
		}
		else
		{
			$this->driver = new \Memcached;
		}

		$this->driver->setOption(\Memcached::OPT_COMPRESSION, $this->options['memcache.compress'] ?: false);
		$this->driver->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
		$serverList = $this->driver->getServerList();

		// If we are using a persistent pool we don't want to add the servers again.
		if (empty($serverList))
		{
			$servers = $this->options['memcache.servers'] ?: array();

			foreach ($servers as $server)
			{
				$this->driver->addServer($server->host, $server->port);
			}
		}
	}
}
