<?php

namespace Molitor\CustomerProduct\Repositories;

use Molitor\Currency\Models\Currency;
use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Currency\Repositories\ExchangeRateRepositoryInterface;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\Product\Models\Product;

class ProductRepository extends \Molitor\Product\Repositories\ProductRepository
{
    public function __construct(
        private CustomerProductRepositoryInterface $customerProductRepository,
        private  CurrencyRepositoryInterface $currencyRepository,
        private ExchangeRateRepositoryInterface $exchangeRateRepository
    )
    {
        parent::__construct();
    }

    public function getMinPrice(Product $product, Currency $currency): ?float
    {
        $minPrice = null;
        $customerProducts = $this->customerProductRepository->getByProduct($product);
        /** @var CustomerProduct $customerProduct */
        foreach ($customerProducts as $customerProduct) {
            $price = $customerProduct->price;
            if($price) {
                $price = $this->exchangeRateRepository->exchange($price, $customerProduct->currency, $currency);
                if($minPrice === null || $price < $minPrice) {
                    $minPrice = $price;
                }
            }
        }
        return $minPrice;
    }

    public function setMinPrice(Product $product): void
    {
        $product->price = $this->getMinPrice($product, $product->currency);
        $product->save();
    }
}
