<?php

namespace Perturbatio\WildCache\Listeners;

use Illuminate\Cache\Events\KeyWritten;
use Perturbatio\WildCache\WildCache;

class WildCacheKeyWritten {
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct() {
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  KeyWritten $event
	 *
	 * @return void
	 */
	public function handle( KeyWritten $event ) {
		app(WildCache::class)->handleWritten($event->key, $event->tags, $event->value, $event->minutes);
	}
}
