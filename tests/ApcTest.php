<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Tests;

use Joomla\Cache;

/**
 * Tests for the Joomla\Cache\Apc class.
 *
 * @since  1.0
 */
class ApcTest extends CacheTest
{
	/**
	 * Tests the Joomla\Cache\Apc::clear method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\Apc::clear
	 * @since   1.0
	 */
	public function testClear()
	{
		$this->assertTrue($this->instance->clear());
	}

	/**
	 * Tests the Joomla\Cache\Apc::hasItem method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\Apc::hasItem
	 * @since   1.0
	 */
	public function testHasItem()
	{
		$this->assertTrue($this->instance->hasItem('foo'));
	}

	/**
	 * Tests the Joomla\Cache\Apc::getItem method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\Apc::getItem
	 * @since   1.0
	 */
	public function testGetItem()
	{
		$this->assertInstanceOf(
			'\Psr\Cache\CacheItemInterface',
			$this->instance->getItem('foo')
		);
	}

	/**
	 * Tests the Joomla\Cache\Apc::deleteItem method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\Apc::deleteItem
	 * @since   1.0
	 */
	public function testDeleteItem()
	{
		$this->assertTrue($this->instance->deleteItem('foo'));
	}

	/**
	 * Tests the Joomla\Cache\Apc::save method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\Apc::save
	 * @since   1.0
	 */
	public function testSave()
	{
		// Create a stub for the CacheItemInterface class.
		$stub = $this->getMockBuilder('\\Psr\\Cache\\CacheItemInterface')
			->getMock();

		$stub->method('get')
			->willReturn('car');

		$stub->method('getKey')
			->willReturn('boo');

		$this->assertTrue($this->instance->save($stub));
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		if (!Cache\Apc::isSupported())
		{
			$this->markTestSkipped('APC Cache Handler is not supported on this system.');
		}

		$this->cacheClass = 'Joomla\\Cache\\Apc';

		parent::setUp();

		// Create a stub for the CacheItemInterface class.
		$stub = $this->getMockBuilder('\\Psr\\Cache\\CacheItemInterface')
			->getMock();

		$stub->method('get')
			->willReturn('bar');

		$stub->method('getKey')
			->willReturn('foo');

		$this->instance->save($stub);
	}
}