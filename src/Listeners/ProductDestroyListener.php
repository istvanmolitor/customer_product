<?php

namespace Molitor\CustomerProduct\Listeners;

use Molitor\CustomerProduct\Repositories\CustomerProductRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductSimilarityRepositoryInterface;
use Molitor\CustomerProduct\Services\SimilarityService;
use Molitor\Product\Events\ProductDestroyEvent;

class ProductDestroyListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private CustomerProductRepositoryInterface $customerProductRepository,
        private SimilarityService                  $similarityService
    )
    {

    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(ProductDestroyEvent $event)
    {
        $this->customerProductRepository->deleteProductConnection($event->product);
        $this->similarityService->deleteByProduct($event->product);
    }
}
