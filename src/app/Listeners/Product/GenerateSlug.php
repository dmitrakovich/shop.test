<?php

namespace App\Listeners\Product;

use App\Events\Products\ProductCreated;
use Illuminate\Support\Str;

class GenerateSlug
{
    /**
     * Handle the event.
     */
    public function handle(ProductCreated $event): void
    {
        $event->product->update([
            'slug' => Str::slug($event->product->shortName()),
        ]);
    }
}
