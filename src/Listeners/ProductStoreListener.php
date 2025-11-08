<?php

namespace Molitor\CustomerProduct\Listeners;

use Molitor\CustomerProduct\Repositories\CustomerProductSimilarityRepositoryInterface;
use Molitor\CustomerProduct\Services\SimilarityService;
use Molitor\Product\Events\ProductStoreEvent;

class ProductStoreListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private SimilarityService $similarityService
    )
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(ProductStoreEvent $event)
    {
        $this->similarityService->updateProduct($event->product);
    }
}
