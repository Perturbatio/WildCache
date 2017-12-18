<?php

namespace Perturbatio\WildCache\Tests;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Perturbatio\WildCache\WildCache;

class WildCacheTest extends \Orchestra\Testbench\TestCase {
	/**
	 * @var
	 */
	public $wildCache;

	public function setUp() {
		parent::setUp();
		$this->app->singleton('wildcache', function ( $app ) {
			return new WildCache();
		});
	}

	/** @test **/
	public function it_returns_a_collection() {
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = $this->app['wildcache'];
		$this->assertInstanceOf('Illuminate\Support\Collection', $wildCache->get('wildcache.test'));
	}

	/** @test **/
	public function it_can_read_an_item_from_the_cache() {
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = $this->app['wildcache'];
		$this->app['cache']->put('wildcache.test', 1, 10);

		$this->assertTrue($wildCache->get('wildcache.test')->first() === 1);
	}

	/** @test **/
	public function it_can_find_items_by_wildcard() {
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = $this->app['wildcache'];
		$this->app['cache']->put('wildcache.test.itemA', 'A', 10);
		$this->app['cache']->put('wildcache.test.itemB', 'B', 10);

		$this->assertEquals('A', $wildCache->get('wildcache.*')->first());
		$this->assertEquals('B', $wildCache->get('wildcache.*')->get('wildcache.test.itemB'));
	}

	/** @test * */
	public function it_can_clear_items_by_wildcard() {
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = $this->app['wildcache'];

		$this->app['cache']->put('wildcache.test.itemA', 'A', 10);
		$this->app['cache']->put('wildcache.test.itemB', 'B', 10);

		$this->assertEquals('A', $wildCache->get('wildcache.*')->first());
		$this->assertEquals('B', $wildCache->get('wildcache.*')->get('wildcache.test.itemB'));

		$wildCache->forget('wildcache.test.*');

		$this->assertNotEquals('A', $wildCache->get('wildcache.*')->first());
		$this->assertNotEquals('B', $wildCache->get('wildcache.*')->get('wildcache.test.itemB'));
	}

	/** @test * */
	public function it_can_clear_items_by_wildcard_preserving_siblings() {
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = $this->app['wildcache'];

		$this->app['cache']->put('wildcache.test.itemA', 'A', 10);
		$this->app['cache']->put('wildcache.test.itemB', 'B', 10);
		$this->app['cache']->put('wildcache.test2.itemC', 'C', 10);
		$this->app['cache']->put('wildcache.test2.itemD', 'D', 10);


		$wildCache->forget('wildcache.test.*');

		$this->assertEquals('C', $wildCache->get('wildcache.test2.itemC')->first());
		$this->assertEquals('D', $wildCache->get('wildcache.test2.itemD')->first());
		$this->assertNotEquals('A', $wildCache->get('wildcache.*')->first());
		$this->assertNotEquals('B', $wildCache->get('wildcache.*')->get('wildcache.test.itemB'));
	}

}
