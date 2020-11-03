<?php

namespace Perturbatio\WildCache\Listeners;

use Illuminate\Cache\Events\KeyForgotten;
use Perturbatio\WildCache\WildCache;

class WildCacheKeyForgotten {

	/**
	 * Handle the event.
	 *
	 * @param  KeyForgotten $event
	 *
	 * @return void
	 */
	public function handle( KeyForgotten $event )
	{
		app(WildCache::class)->handleForgotten($event->key, $event->tags);
	}
}
