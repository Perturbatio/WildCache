<?php

namespace Perturbatio\WildCache\Tests;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Orchestra\Testbench\TestCase;
use Perturbatio\WildCache\WildCache;
use Perturbatio\WildCache\WildCacheProvider;
use stdClass;

class WildCacheTest extends TestCase {
	/**
	 * @var \Illuminate\Support\Facades\Cache
	 */
	protected $appCache;

	public function setUp(): void
	{
		parent::setUp();

		$this->wildCache = app('wildcache');

		$this->appCache = $this->app['cache'];
	}

	/**
	 * @param \Illuminate\Foundation\Application $app
	 *
	 * @return array
	 */
	protected function getPackageProviders( $app )
	{
		return [WildCacheProvider::class];
	}

	protected function getPackageAliases( $app )
	{
		return [
			'wildcache' => WildCache::class,
		];
	}
	/** @test * */
	public function it_returns_a_collection()
	{
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = app('wildcache');
		$this->assertInstanceOf('Illuminate\Support\Collection', $wildCache->get('wildcache.test'));
	}

	/** @test * */
	public function it_can_read_an_item_from_the_cache()
	{
		$cacheKey   = 'wildcache.test';
		$cacheValue = 9999;
		$this->appCache->put($cacheKey, $cacheValue, now()->addMinutes(10));

		$this->assertTrue(app('wildcache')->get($cacheKey)->first() === $cacheValue);
	}

	/** @test * */
	public function it_can_find_items_by_wildcard()
	{
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = app('wildcache');
		$this->appCache->put('wildcache.test.itemA', 'A', 10);
		$this->appCache->put('wildcache.test.itemB', 'B', 10);

		$this->assertEquals('A', $wildCache->get('wildcache.*')->first());
		$this->assertEquals('B', $wildCache->get('wildcache.*')->get('wildcache.test.itemB'));
	}

	/** @test * */
	public function it_can_clear_items_by_wildcard()
	{
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = app('wildcache');

		$this->appCache->put('wildcache.test.itemA', 'A', 10);
		$this->appCache->put('wildcache.test.itemB', 'B', 10);

		$this->assertEquals('A', $wildCache->get('wildcache.*')->first());
		$this->assertEquals('B', $wildCache->get('wildcache.*')->get('wildcache.test.itemB'));

		$wildCache->forget('wildcache.test.*');

		$this->assertNotEquals('A', $wildCache->get('wildcache.*')->first());
		$this->assertNotEquals('B', $wildCache->get('wildcache.*')->get('wildcache.test.itemB'));
	}

	/**
	 * @test
	 * @skip
	 *
	 */
	public function it_can_clear_items_by_wildcard_preserving_siblings()
	{
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = app('wildcache');

		$this->appCache->put('wildcache.test.itemA', 'A', 10);
		$this->appCache->put('wildcache.test.itemB', 'B', 10);
		$this->appCache->put('wildcache.test2.itemC', 'C', 10);
		$this->appCache->put('wildcache.test2.itemD', 'D', 10);


		$wildCache->forget('wildcache.test.*');

		$this->assertEquals('C', $wildCache->get('wildcache.test2.itemC')->first(), "test2.itemC has an invalid value");
		$this->assertEquals('D', $wildCache->get('wildcache.test2.itemD')->first(), "test2.itemD has an invalid value");
		$this->assertNotEquals('A', $wildCache->get('wildcache.*')->first(), "test.itemA has not been cleared");
		$this->assertNotEquals('B', $wildCache->get('wildcache.*')->get("test.itemB has not been cleared"));
	}

	/** @test * */
	public function it_can_store_and_retrieve_objects()
	{
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = app('wildcache');
		$obj       = new stdClass();
		$obj->id   = 1;
		$obj->text = "Lorem ipsum dolor sit amet";

		$this->appCache->put('wildcache.test.obj', $obj, 10);

		$result = $wildCache->first('wildcache.test.obj');

		$this->assertEquals($obj, $result);

	}

	/** @test * */
	public function it_can_put_and_retrieve_multiple_keys()
	{
		/**
		 * @var WildCache $wildCache
		 */
		$wildCache = app('wildcache');

		$wildCache->putMany([
			'wildcache.test.itemA'  => 'A',
			'wildcache.test.itemB'  => 'B',
			'wildcache.test2.itemC' => 'C',
			'wildcache.test2.itemD' => 'D',
		], 10);

		$vals = $wildCache->many([
			'wildcache.test.*',
			'wildcache.test2.*',
			'wildcache.test3.*',
		]);

		$this->assertEquals('A', $vals['wildcache.test.itemA']);
		$this->assertEquals('B', $vals['wildcache.test.itemB']);
		$this->assertEquals('C', $vals['wildcache.test2.itemC']);
		$this->assertEquals('D', $vals['wildcache.test2.itemD']);
		$this->assertEquals(null, $vals['wildcache.test3.*'], 'An invalid key did not return null');
	}


}
