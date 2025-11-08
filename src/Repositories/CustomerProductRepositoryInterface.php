<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\LazyCollection;
use Molitor\Currency\Models\Currency;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\Product\Libs\Keywords;
use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductUnit;

interface CustomerProductRepositoryInterface {

    public function count(Customer $customer): int;

    public function getBySku(Customer $customer, string $sku): ?CustomerProduct;

    public function save(
        Customer    $customer,
        string      $sku,
        string      $name,
        string      $description = null,
        string      $url = null,
        float       $price = null,
        Currency    $currency = null,
        int         $stock = null,
        ProductUnit $productUnit = null
    ): CustomerProduct;

    public function copyProduct(CustomerProduct $customerProduct): Product;

    /**
     * Leválasztja az ügyfél terméket
     * @param CustomerProduct $customerProduct
     * @return bool
     */
    public function notSame(CustomerProduct $customerProduct): bool;

    /**
     * A tetrmékhez hozzákapcsolja az ügyfél terméket
     * @param CustomerProduct $customerProduct
     * @param Product $product
     * @return $this
     */
    public function joinProduct(CustomerProduct $customerProduct, Product $product): bool;

    public function joinOrCreateProduct(CustomerProduct $customerProduct): Product;

    public function copyNextCustomerProduct(Customer $customer): bool;

    public function getAllCountByCustomer(Customer $customer): int;

    public function getReadyCountByCustomer(Customer $customer): int;

    public function deleteProduct(CustomerProduct $customerProduct): self;

    public function getByCustomer(Customer $customer, array $with = []): Collection;

    public function getSameByProduct(CustomerProduct $product): Collection;

    public function getSameByCustomer(Customer $customer): Collection;

    public function deleteProducts(Customer $customer): self;

    public function getCustomerOptions(): Collection;

    public function getByProduct(Product $product): Collection;

    public function getByIds(array $ids): Collection;

    public function getUnhandledCustomerProducts(): LazyCollection;

    public function getAll(): LazyCollection;

    public function getNotJointCustomerProducts(Product $product): LazyCollection;

    public function deleteProductConnection(Product $product): void;

    public function getOtherCustomerProducts(CustomerProduct $customerProduct): LazyCollection;

    public function getProductIdsByCustomer($customer): array;

    public function joinCustomerProducts(CustomerProduct $customerProduct1, CustomerProduct $customerProduct2): bool;

    public function getNext(CustomerProduct $customerProduct): ?CustomerProduct;

    public function getPrev(CustomerProduct $customerProduct): ?CustomerProduct;

    /**
     * Cikkszám alapján összeköti a termékeket.
     * @param CustomerProduct $customerProduct
     * @return void
     */
    public function joinBySku(CustomerProduct $customerProduct): bool;

    public function priceComparison(Collection $customers, Currency $currency): array;
}
