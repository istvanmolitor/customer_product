<?php

namespace Molitor\CustomerProduct\Listeners;

use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductRepository;

class CustomerDestroyListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        (new CustomerProductRepository())->deleteProducts($event->customer);
        (new CustomerProductCategoryRepository())->deleteProductCategories($event->customer);
    }
}
