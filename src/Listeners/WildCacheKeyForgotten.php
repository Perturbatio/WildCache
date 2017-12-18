<?php

namespace Perturbatio\WildCache\Listeners;

use Illuminate\Cache\Events\KeyForgotten;

class WildCacheKeyForgotten {
	/**
	 * Create the event listener.
	 *
	 */
	public function __construct() {
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  KeyForgotten $event
	 *
	 * @return void
	 */
	public function handle( KeyForgotten $event ) {
		app('wildcache')->handleForgotten($event->key, $event->tags);
	}
}
