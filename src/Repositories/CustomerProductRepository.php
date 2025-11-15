<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\LazyCollection;
use Molitor\Currency\Models\Currency;
use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Currency\Repositories\ExchangeRateRepositoryInterface;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Services\SimilarityService;
use Molitor\Product\Libs\Keywords;
use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductUnit;
use Molitor\Product\Repositories\ProductImageRepositoryInterface;
use Molitor\Product\Repositories\ProductRepositoryInterface;
use Molitor\Product\Repositories\ProductUnitRepositoryInterface;

class CustomerProductRepository implements CustomerProductRepositoryInterface
{
    private CustomerProduct $customerProduct;

    public function __construct(
        private CurrencyRepositoryInterface                       $currencyRepository,
        private ProductUnitRepositoryInterface                    $productUnitRepository,
        private ProductRepositoryInterface                        $productRepository,
        private ProductImageRepositoryInterface                   $productImageRepository,
        private CustomerProductAttributeRepositoryInterface       $customerProductFieldValueRepository,
        private CustomerProductCategoryProductRepositoryInterface $customerProductCategoryProductRepository,
        private CustomerProductImageRepositoryInterface           $customerProductImageRepository,
        private ExchangeRateRepositoryInterface                   $exchangeRateRepository
    )
    {
        $this->customerProduct = new CustomerProduct();
    }

    public function count(Customer $customer): int
    {
        return $this->customerProduct->where('customer_id', $customer->id)->count();
    }

    public function getBySku(Customer $customer, string $sku): ?CustomerProduct
    {
        if (empty($sku)) {
            return null;
        }
        return $this->customerProduct->where('customer_id', $customer->id)->where('sku', $sku)->first();
    }

    public function save(
        Customer    $customer,
        string      $sku,
        string      $name,
        string      $description = null,
        string      $url = null,
        float       $price = null,
        Currency    $currency = null,
        int         $stock = null,
        ProductUnit $productUnit = null,
    ): CustomerProduct
    {

        $currency = $this->currencyRepository->make($currency);
        $productUnit = $this->productUnitRepository->make($productUnit);

        $customerProduct = $this->getBySku($customer, $sku);
        if ($customerProduct) {
            $customerProduct->name = $name;
            $customerProduct->description = $description;
            $customerProduct->url = $url;
            $customerProduct->price = $price;
            $customerProduct->currency_id = $currency->id;
            $customerProduct->stock = $stock;
            $customerProduct->product_unit_id = $productUnit->id;

            $customerProduct->save();

        } else {
            $customerProduct = CustomerProduct::create(
                [
                    'customer_id' => $customer->id,
                    'sku' => $sku,
                    'name' => $name,
                    'description' => $description,
                    'url' => $url,
                    'price' => $price,
                    'currency_id' => $currency->id,
                    'stock' => $stock,
                    'product_unit_id' => $productUnit->id,
                ]
            );
        }

        return $customerProduct;
    }

    public function copyProduct(CustomerProduct $customerProduct): Product
    {
        $product = $this->productRepository->save(
            $customerProduct->sku,
            $customerProduct->name,
            $customerProduct->description,
            (float)$customerProduct->price,
            $customerProduct->currency,
            $customerProduct->productUnit
        );

        $this->joinProduct($customerProduct, $product);

        foreach ($customerProduct->customerProductImages as $customerProductImage) {
            $this->productImageRepository->saveUrl($product, $customerProductImage->url, $customerProductImage->title);
        }

        $this->customerProductFieldValueRepository->overwrite($customerProduct, $product);

        return $product;
    }

    /**
     * Összeköt két ügyfél terméket
     * @param CustomerProduct $customerProduct1
     * @param CustomerProduct $customerProduct2
     * @return void
     */
    private function same(CustomerProduct $customerProduct1, CustomerProduct $customerProduct2): void
    {
        if (
            $customerProduct1->customer_id === $customerProduct2->customer_id ||
            $customerProduct1->product_id ||
            $customerProduct2->product_id
        ) {
            return;
        }

        $id1 = $customerProduct1->same_customer_product ?: $customerProduct1->id;
        $id2 = $customerProduct2->same_customer_product ?: $customerProduct2->id;
        $sameId = min($id1, $id2);

        $this->setSameCustomerProducts($customerProduct1, $sameId);
        $this->setSameCustomerProducts($customerProduct2, $sameId);

        $this->customerProductSimilarityRepository->updateCustomerProduct($customerProduct1);
        $this->customerProductSimilarityRepository->updateCustomerProduct($customerProduct2);
    }

    /**
     * Leválasztja az ügyfél terméket
     * @param CustomerProduct $customerProduct
     * @return bool
     */
    public function notSame(CustomerProduct $customerProduct): bool
    {
        return $this->setSameCustomerProducts($customerProduct, null);
    }

    private function setSameCustomerProducts(CustomerProduct $customerProduct, ?int $sameId): bool
    {
        if ($sameId === $customerProduct->same_customer_product) {
            return false;
        }

        if ($sameId === null) {
            $minId = $this->customerProduct->where('customer_id', $customerProduct->customer_id)
                ->where('same_customer_product', $customerProduct->same_customer_product)
                ->where('id', '<>', $customerProduct->id)
                ->min('id');

            if ($minId) {
                $this->customerProduct->where('customer_id', $customerProduct->customer_id)
                    ->where('same_customer_product', $customerProduct->same_customer_product)
                    ->where('id', '<>', $customerProduct->id)
                    ->update([
                        'same_customer_product' => $minId
                    ]);
            }

            $customerProduct->same_customer_product = null;
            $customerProduct->save();

        } elseif ($customerProduct->same_customer_product) {
            $this->customerProduct->where('customer_id', $customerProduct->customer_id)
                ->where('same_customer_product', $customerProduct->same_customer_product)
                ->update([
                    'same_customer_product' => $sameId
                ]);
        } else {
            $customerProduct->same_customer_product = $sameId;
            $customerProduct->save();
        }

        return true;
    }

    /**
     * A tetrmékhez hozzákapcsolja az ügyfél terméket
     * @param CustomerProduct $customerProduct
     * @param Product $product
     * @return $this
     */
    public function joinProduct(CustomerProduct $customerProduct, Product $product): bool
    {
        //Ha már hozzá van kötve egy termékhez akkor nem módosítjuk
        if ($customerProduct->product_id) {
            return false;
        }

        $sameCustomerProductId = $customerProduct->same_customer_product;
        if ($sameCustomerProductId) {
            $customerProducts = $this->customerProduct->where('same_customer_product', $sameCustomerProductId)
                ->whereNull('product_id')
                ->get();
        } else {
            $customerProducts = [$customerProduct];
        }

        $this->customerProductSimilarityRepository->updateProduct($product);

        foreach ($customerProducts as $customerProduct) {
            $customerProduct->product_id = $product->id;
            $customerProduct->same_customer_product = null;
            $customerProduct->save();
            $this->customerProductSimilarityRepository->updateCustomerProduct($customerProduct);
        }

        return true;
    }

    public function joinOrCreateProduct(CustomerProduct $customerProduct): Product
    {
        $product = $customerProduct->product;
        if ($product) {
            return $product;
        }

        return $this->copyProduct($customerProduct);
    }

    public function copyNextCustomerProduct(Customer $customer): bool
    {
        $customerProduct = $this->customerProduct
            ->where('customer_id', $customer->id)
            ->whereNull('product_id')
            ->with('currency')
            ->first();
        if ($customerProduct) {
            $this->copyProduct($customerProduct);
            return true;
        }
        return false;
    }

    public function getAllCountByCustomer(Customer $customer): int
    {
        return $this->customerProduct
            ->where('customer_id', $customer->id)
            ->count();
    }

    public function getReadyCountByCustomer(Customer $customer): int
    {
        return $this->customerProduct
            ->where('customer_id', $customer->id)
            ->whereNotNull('product_id')
            ->count();
    }

    public function deleteProduct(CustomerProduct $customerProduct): self
    {
        $this->customerProductCategoryProductRepository->deleteByCustomerProduct($customerProduct);
        $this->customerProductFieldValueRepository->deleteProductValues($customerProduct);
        $this->customerProductImageRepository->clearImages($customerProduct);
        $customerProduct->delete();
        return $this;
    }

    public function getByCustomer(Customer $customer, array $with = []): Collection
    {
        return $this->customerProduct->where('customer_id', $customer->id)->with($with)->get();
    }

    public function getSameByProduct(CustomerProduct $product): Collection
    {
        return $this->customerProduct->where('same_customer_product', $product->same_customer_product)->get();
    }

    public function getSameByCustomer(Customer $customer): Collection
    {
        return $this->customerProduct->where('customer_id', $customer->id)->whereNotNull('same_customer_product')->get();
    }

    public function deleteProducts(Customer $customer): self
    {
        foreach ($this->getByCustomer($customer) as $customerProduct) {
            $this->deleteProduct($customerProduct);
        }
        return $this;
    }

    public function getCustomerOptions(): Collection
    {
        $customerIds = $this->customerProduct->groupBy('customer_id')->select('customer_id')->pluck('customer_id');
        return Customer::whereIn('id', $customerIds)->orderBy('name')->select('id AS value', 'name AS text')->get();
    }

    public function getByProduct(Product $product): Collection
    {
        return $this->customerProduct->where('product_id', $product->id)->with('customer', 'customerProductImages')->orderBy('name')->get();
    }

    public function getByIds(array $ids): Collection
    {
        return $this->customerProduct->whereIn('id', $ids)->with('customer')->get();
    }

    public function getUnhandledCustomerProducts(): LazyCollection
    {
        return $this->customerProduct->whereNull('product_id')->cursor();
    }

    public function getAll(): LazyCollection
    {
        return $this->customerProduct->cursor();
    }

    public function getNotJointCustomerProducts(Product $product): LazyCollection
    {
        //Ügyfelek akiknek a listájában már van ez a termék
        $customerIds = $this->customerProduct->where('product_id', $product->id)->groupBy('customer_id')->select('customer_id')->pluck('customer_id')->toArray();
        return $this->customerProduct->whereNull('product_id')->whereNotIn('customer_id', $customerIds)->cursor();
    }

    public function deleteProductConnection(Product $product): void
    {
        $this->customerProduct->where('product_id', $product->id)->update([
            'product_id' => null
        ]);
    }

    public function getOtherCustomerProducts(CustomerProduct $customerProduct): LazyCollection
    {
        return $this->customerProduct->where('customer_id', '<>', $customerProduct->customer_id)->cursor();
    }

    public function getProductIdsByCustomer($customer): array
    {
        return $this->customerProduct
            ->where('customer_id', $customer->id)
            ->whereNotNull('product_id')
            ->select('product_id')
            ->pluck('product_id')
            ->toArray();
    }

    public function joinCustomerProducts(CustomerProduct $customerProduct1, CustomerProduct $customerProduct2): bool
    {
        if ($customerProduct1->product && $customerProduct2->product) {
            return false;
        } elseif (!$customerProduct1->product && $customerProduct2->product) {
            $this->joinProduct($customerProduct1, $customerProduct2->product);
        } elseif ($customerProduct1->product && !$customerProduct2->product) {
            $this->joinProduct($customerProduct2, $customerProduct1->product);
        } else {
            $this->same($customerProduct1, $customerProduct2);
        }
        return true;
    }

    public function getNext(CustomerProduct $customerProduct): ?CustomerProduct
    {
        return $this->customerProduct
            ->where('customer_id', $customerProduct->customer_id)
            ->where('id', '>', $customerProduct->id)
            ->first();
    }

    public function getPrev(CustomerProduct $customerProduct): ?CustomerProduct
    {
        return $this->customerProduct
            ->where('customer_id', $customerProduct->customer_id)
            ->where('id', '<', $customerProduct->id)
            ->first();
    }

    /**
     * Cikkszám alapján összeköti a termékeket.
     * @param CustomerProduct $customerProduct
     * @return void
     */
    public function joinBySku(CustomerProduct $customerProduct): bool
    {
        if ($customerProduct->product_id) {
            return false;
        }

        $product = $this->productRepository->getBySku($customerProduct->sku);
        if ($product) {
            $this->joinProduct($customerProduct, $product);
            return true;
        }

        //Másik ügyfél terméke ami még nincs összekötve.
        $similarCustomerProduct = $this->customerProduct
            ->where('sku', $customerProduct->sku)
            ->where('customer_id', '<>', $customerProduct->customer_id)
            ->whereNotNull('product_id')
            ->first();

        if ($similarCustomerProduct) {
            $this->joinCustomerProducts($similarCustomerProduct, $customerProduct);
            return true;
        }

        //Másik ügyfél terméke ezen a cikkszámon ami már össze van kötve.
        $similarCustomerProduct = $this->customerProduct
            ->where('sku', $customerProduct->sku)
            ->where('customer_id', '<>', $customerProduct->customer_id)
            ->whereNull('product_id')
            ->first();

        if ($similarCustomerProduct) {
            $this->joinCustomerProducts($similarCustomerProduct, $customerProduct);
            return true;
        }

        return false;
    }

    public function priceComparison(Collection $customers, Currency $currency): array
    {
        $data = [];
        foreach ($customers as $customer) {
            $customerProducts = $this->getSameByCustomer($customer);
            /** @var CustomerProduct $customerProduct */
            foreach ($customerProducts as $customerProduct) {
                if (!array_key_exists($customerProduct->same_customer_product, $data)) {
                    $data[$customerProduct->same_customer_product] = [];
                    foreach ($customers as $customer) {
                        $data[$customerProduct->same_customer_product][$customer->id] = null;
                    }
                }
                $data[$customerProduct->same_customer_product][$customerProduct->customer_id] = $this->exchangeRateRepository->exchange(
                    (float)$customerProduct->price,
                    $customerProduct->currency,
                    $currency
                );
            }
        }

        return $data;
    }

    public function getById(int $id): CustomerProduct|null
    {
        return $this->customerProduct->find($id);
    }
}
