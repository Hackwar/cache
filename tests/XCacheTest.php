<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Tests;

use Joomla\Cache;

/**
 * Tests for the Joomla\Cache\XCache class.
 *
 * @since  1.0
 */
class XCacheTest extends CacheTest
{
	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		if (!Cache\XCache::isSupported())
		{
			$this->markTestSkipped('XCache Cache Handler is not supported on this system.');
		}

		$this->cacheClass = 'Joomla\\Cache\\XCache';
		parent::setUp();
	}
}