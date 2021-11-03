<?php

namespace Perturbatio\WildCache\Tests;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;
use Perturbatio\WildCache\WildCache;
use Perturbatio\WildCache\WildCacheProvider;
use stdClass;

class WildCacheTest extends TestCase {
	/**
	 * @var \Illuminate\Support\Facades\Cache
	 */
	protected $appCache;

	/**
	 * @var \Perturbatio\WildCache\WildCache
	 */
	private $wildCache;

	/**
	 * Setup the test environment.
	 *
	 * @return void
	 */
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
		$this->assertInstanceOf('Illuminate\Support\Collection', $this->wildCache->get('wildcache.test'));
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
		$this->appCache->put('wildcache.test.itemA', 'A', 10);
		$this->appCache->put('wildcache.test.itemB', 'B', 10);

		$this->assertEquals('A', $this->wildCache->get('wildcache.*')->first());
		$this->assertEquals('B', $this->wildCache->get('wildcache.*')->get('wildcache.test.itemB'));
	}

	/** @test * */
	public function it_can_clear_items_by_wildcard()
	{
		$this->appCache->put('wildcache.test.itemA', 'A', 10);
		$this->appCache->put('wildcache.test.itemB', 'B', 10);

		$this->assertEquals('A', $this->wildCache->get('wildcache.*')->first());
		$this->assertEquals('B', $this->wildCache->get('wildcache.*')->get('wildcache.test.itemB'));

		$this->wildCache->forget('wildcache.test.*');

		$this->assertNotEquals('A', $this->wildCache->get('wildcache.*')->first());
		$this->assertNotEquals('B', $this->wildCache->get('wildcache.*')->get('wildcache.test.itemB'));
	}

	/**
	 * @test
	 *
	 */
	public function it_can_clear_items_by_wildcard_preserving_siblings()
	{
		$this->appCache->put('wildcache.test.itemA', 'A', 10);
		$this->appCache->put('wildcache.test.itemB', 'B', 10);
		$this->appCache->put('wildcache.test2.itemC', 'C', 10);
		$this->appCache->put('wildcache.test2.itemD', 'D', 10);

		$this->wildCache->forget('wildcache.test.*');

		$this->assertEquals('C', $this->wildCache->get('wildcache.test2.itemC')->first(), "test2.itemC has an invalid value");
		$this->assertEquals('D', $this->wildCache->get('wildcache.test2.itemD')->first(), "test2.itemD has an invalid value");
		$this->assertNotEquals('A', $this->wildCache->get('wildcache.*')->first(), "test.itemA has not been cleared");
		$this->assertNotEquals('B', $this->wildCache->get('wildcache.*')->get("test.itemB has not been cleared"));
	}

	/** @test * */
	public function it_can_store_and_retrieve_objects()
	{
		$obj       = new stdClass();
		$obj->id   = 1;
		$obj->text = "Lorem ipsum dolor sit amet";

		$this->appCache->put('wildcache.test.obj', $obj, 10);

		$result = $this->wildCache->first('wildcache.test.obj');

		$this->assertEquals($obj, $result);

	}

	/** @test * */
	public function it_can_put_and_retrieve_multiple_keys()
	{
		$this->wildCache->putMany([
			'wildcache.test.itemA'  => 'A',
			'wildcache.test.itemB'  => 'B',
			'wildcache.test2.itemC' => 'C',
			'wildcache.test2.itemD' => 'D',
		], 10);

		$vals = $this->wildCache->many([
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

	/**
	 * Test that if the cache is added with the remember functionality, the package still removes the data.
	 */
	public function test_it_invalidates_facade_cache()
	{
		Cache::remember('wildcache.test.itemA', 2880, function () {
			return 'A';
		});
		Cache::remember('wildcache.test.itemB', 2880, function () {
			return 'B';
		});

		$this->wildCache->forget('wildcache.*');
		$this->assertFalse(Cache::has('windcache.test.itemA'));
		$this->assertFalse(Cache::has('windcache.test.itemB'));
	}

	public function test_load_map_returns_empty_array_if_key_does_not_exist()
	{
		$res = $this->wildCache->loadMap();
		$this->assertEquals([], $res);
	}
}
