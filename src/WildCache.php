<?php

namespace Perturbatio\WildCache;

use Illuminate\Cache\RetrievesMultipleKeys;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;

/**
 * Created by kris with PhpStorm.
 * User: kris
 * Date: 05/11/16
 * Time: 00:31
 */

/**
 * Class WildCache
 * @package App\Libraries
 */
class WildCache {

	use RetrievesMultipleKeys;

	use Macroable;

	/**
	 * @var string
	 */
	public $separator = '.';

	/**
	 * @var string
	 */
	public $cacheKey = '__' . __CLASS__ . '.map';

	/**
	 * @var array
	 */
	public $map;

	/**
	 * WildCache constructor.
	 */
	public function __construct( $separator = '.' ) {
		$this->map       = $this->loadMap();
		$this->separator = $separator;
		$this->registerListeners();
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function forget( $key ) {
		$result = false;
		if ($key === $this->cacheKey) {
			return false;
		}

		$items = $this->findItems($key);
		if ($items->count() > 0) {
			$items->each(function ( $item ) {
				app('cache')->forget($item);
			});

			$result = true;
			$this->removeKey($key);
		}


		return $result;
	}

	/**
	 * Get an item or items by key, this function will always
	 * return a collection regardless of the number of results
	 *
	 * @param $key
	 *
	 * @return Collection
	 */
	public function get( $key, $default = null ) {
		$result = [];
		if ($key === $this->cacheKey) {
			return collect();
		}

		$items = $this->findItems($key);

		if ($items->count() > 0) {
			$result = $items->reduce(function ( $result, $item ) {
				$result[ $item ] = app('cache')->get($item);

				return $result;
			}, []);
		}

		return count($result) > 0 ? collect($result) : collect($default);
	}

	/**
	 * Get the first item matching key
	 *
	 * @param               $key
	 * @param null          $default
	 *
	 * @return mixed
	 */
	public function first( $key, $default = null ) {
		return $this->get($key)->first(null, $default);
	}

	/**
	 * @param $key
	 * @param $value
	 * @param $minutes
	 */
	public function put( $key, $value, $minutes ) {
		app('cache')->put($key, $value, $minutes);
	}

	/**
	 * @return mixed
	 */
	public function loadMap() {
		try {
			return app('cache')->get($this->cacheKey);
		} catch (EntryNotFoundException $exception) {
			//handle exception
			return [];
		}
	}

	/**
	 * @return mixed
	 */
	public function writeMap() {
		return app('cache')->forever($this->cacheKey, $this->map);
	}

	/**
	 * Purge (clear) the entire map
	 * @return mixed
	 */
	public function purgeMap() {
		return app('cache')->forget($this->cacheKey);
	}

	/**
	 * @param $key
	 * @param $tags
	 *
	 * @return mixed
	 */
	public function handleForgotten( $key, $tags ) {
		if ($key === $this->cacheKey) {
			return false;
		}

		array_forget($this->map, $key);

		$parts = explode($this->separator, $key);

		//clear as far up the tree as we can
		$canExit = false;
		while ( !$canExit && empty($parts)) {
			$partKey = implode($this->separator, $parts);
			//as long as there's only one item in the current path, we're safe to purge it
			if (count(array_get($this->map, $partKey)) < 2) {
				array_forget($this->map, $partKey);
				array_pop($parts);
			} else {
				$canExit = true;
			}

		}

		return $this->writeMap();
	}

	/**
	 * @param $key
	 * @param $tags
	 * @param $value
	 * @param $minutes
	 *
	 * @return mixed
	 */
	public function handleWritten( $key, $tags, $value, $minutes ) {
		if ($key === $this->cacheKey) {
			return false;
		}
		array_set($this->map, $key, $key);

		return $this->writeMap();
	}

	/**
	 * @param $key
	 *
	 * @return Collection
	 */
	protected function findItems( $key ) {
		//trim trailing .* to allow "cache.items" to be the same as "cache.items.*
		$key = rtrim($key, $this->separator . '*');

		return collect(array_get($this->map, $key))->flatten();
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function removeKey( $key ) {
		array_set($this->map, $key, null);

		return $this->writeMap();
	}

	/**
	 *
	 */
	public function registerListeners() {
		app('events')->listen('Illuminate\Cache\Events\KeyForgotten', 'Perturbatio\WildCache\Listeners\WildCacheKeyForgotten');
		app('events')->listen('Illuminate\Cache\Events\KeyWritten', 'Perturbatio\WildCache\Listeners\WildCacheKeyWritten');
	}

	/**
	 * Retrieve multiple items from the cache by key.
	 *
	 * Items not found in the cache will have a null value.
	 *
	 * @param array $keys
	 *
	 * @return Collection
	 */
	public function many( array $keys ) {
		$result = collect();

		foreach ($keys as $key) {
			$value = $this->get($key);
			if ($value->count() > 0) {
				$result = $result->merge($value);
			} else {
				$result->put($key, null);
			}
		}

		return $result;
	}


}
