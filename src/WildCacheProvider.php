<?php

namespace Perturbatio\WildCache;

use Illuminate\Support\ServiceProvider;

class WildCacheProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->app('events')->listen('Illuminate\Cache\Events\KeyForgotten', 'Perturbatio\WildCache\WildCacheKeyForgotten');
		$this->app('events')->listen('Illuminate\Cache\Events\KeyWritten', 'Perturbatio\WildCache\WildCacheKeyWritten');
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

	/**
	 * Get the config path
	 *
	 * @return string
	 */
	protected function getConfigPath() {
		return config_path('wildcache.php');
	}

	/**
	 * Publish the config file
	 *
	 * @param  string $configPath
	 */
	protected function publishConfig( $configPath ) {
		$this->publishes([$configPath => config_path('wildcache.php')], 'config');
	}

}
