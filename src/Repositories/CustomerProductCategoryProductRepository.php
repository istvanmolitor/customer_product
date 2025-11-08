<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Models\CustomerProductCategoryProduct;

class CustomerProductCategoryProductRepository implements CustomerProductCategoryProductRepositoryInterface
{
    private CustomerProductCategoryProduct $customerProductCategoryProduct;

    public function __construct()
    {
        $this->customerProductCategoryProduct = new CustomerProductCategoryProduct();
    }

    public function exists(CustomerProductCategory $category, CustomerProduct $product): bool
    {
        return $this->customerProductCategoryProduct
                ->where('customer_product_category_id', $category->id)
                ->where('customer_product_id', $product->id)
                ->count() > 0;
    }

    public function setValue(CustomerProductCategory $category, CustomerProduct $product, bool $value): void
    {
        if ($value !== $this->exists($category, $product)) {
            if ($value) {
                $this->customerProductCategoryProduct->create([
                    'customer_product_category_id' => $category->id,
                    'customer_product_id' => $product->id
                ]);
            } else {
                $this->customerProductCategoryProduct
                    ->where('customer_product_category_id', $category->id)
                    ->where('customer_product_id', $product->id)
                    ->delete();
            }
        }
    }

    public function deleteByCustomerProduct(CustomerProduct $customerProduct): void
    {
        $this->customerProductCategoryProduct
            ->where('customer_product_id', $customerProduct->id)
            ->delete();
    }

    public function deleteByCustomerProductCategory(CustomerProductCategory $customerProductCategory): void
    {
        $this->customerProductCategoryProduct
            ->where('customer_product_category_id', $customerProductCategory->id)
            ->delete();
    }

    public function getCustomerProductIdsByCustomerProductCategory(CustomerProductCategory $customerProductCategory): array
    {
        return $this->customerProductCategoryProduct
            ->where('customer_product_category_id', $customerProductCategory->id)
            ->pluck('customer_product_id')
            ->toArray();
    }

    public function getCustomerProductCategoryIdsByCustomerProduct(CustomerProduct $customerProduct): array
    {
        return $this->customerProductCategoryProduct
            ->where('customer_product_id', $customerProduct->id)
            ->pluck('customer_product_category_id')
            ->toArray();
    }

    public function setProductValues(CustomerProduct $customerProduct, array $customerProductCategoryIds, bool $value): void
    {
        $this->deleteByCustomerProduct($customerProduct);
        if(in_array(0, $customerProductCategoryIds)) {
            $rootCategory = new CustomerProductCategory();
            $rootCategory->id = 0;
            $this->setValue($rootCategory, $customerProduct, $value);
        }
        $categories = (new CustomerProductCategoryRepository())->getByIds($customerProduct->customer, $customerProductCategoryIds);
        foreach ($categories as $category) {
            $this->setValue($category, $customerProduct, $value);
        }
    }
}
