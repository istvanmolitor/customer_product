<?php

namespace Molitor\CustomerProduct\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Molitor\Currency\Repositories\CurrencyRepository;
use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Customer\Events\CustomerDestroyEvent;
use Molitor\Customer\Events\CustomerUpdateEvent;
use Molitor\CustomerProduct\Listeners\CustomerDestroyListener;
use Molitor\CustomerProduct\Listeners\CustomerUpdateListener;
use Molitor\CustomerProduct\Listeners\ProductDestroyListener;
use Molitor\CustomerProduct\Listeners\ProductStoreListener;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryProductRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryProductRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductAttributeRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductAttributeRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductImageRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductImageRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductSimilarityRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductSimilarityRepositoryInterface;
use Molitor\Product\Events\ProductDestroyEvent;
use Molitor\Product\Events\ProductStoreEvent;

class CustomerProductServiceProvider extends ServiceProvider
{
    protected $listen = [
        CustomerUpdateEvent::class => [
            CustomerUpdateListener::class,
        ],
        CustomerDestroyEvent::class => [
            CustomerDestroyListener::class,
        ],
        ProductStoreEvent::class => [
            ProductStoreListener::class,
        ],
        ProductDestroyEvent::class => [
            ProductDestroyListener::class,
        ],
    ];

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'customer_product');
    }

    public function register()
    {
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        $this->app->bind(CustomerProductCategoryProductRepositoryInterface::class, CustomerProductCategoryProductRepository::class);
        $this->app->bind(CustomerProductCategoryRepositoryInterface::class, CustomerProductCategoryRepository::class);
        $this->app->bind(CustomerProductRepositoryInterface::class, CustomerProductRepository::class);
        $this->app->bind(CustomerProductImageRepositoryInterface::class, CustomerProductImageRepository::class);
        $this->app->bind(CustomerProductAttributeRepositoryInterface::class, CustomerProductAttributeRepository::class);
        $this->app->bind(CustomerProductSimilarityRepositoryInterface::class, CustomerProductSimilarityRepository::class);
    }
}
