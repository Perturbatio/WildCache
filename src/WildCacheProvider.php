<?php

namespace Perturbatio\WildCache;

use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\ServiceProvider;
use Perturbatio\WildCache\Listeners\WildCacheKeyForgotten;
use Perturbatio\WildCache\Listeners\WildCacheKeyWritten;

class WildCacheProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->app['events']->listen(KeyForgotten::class, WildCacheKeyForgotten::class);
		$this->app['events']->listen(KeyWritten::class, WildCacheKeyWritten::class);
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		$this->app->singleton(WildCache::class, function ( $app ) {
			return new WildCache();
		});

        $this->app->alias('wildcache', WildCache::class);
	}

}
