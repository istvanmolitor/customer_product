<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;

interface CustomerProductCategoryProductRepositoryInterface
{
    public function exists(CustomerProductCategory $category, CustomerProduct $product): bool;

    public function setValue(CustomerProductCategory $category, CustomerProduct $product, bool $value): void;

    public function deleteByCustomerProduct(CustomerProduct $customerProduct): void;

    public function deleteByCustomerProductCategory(CustomerProductCategory $customerProductCategory): void;

    public function getCustomerProductIdsByCustomerProductCategory(CustomerProductCategory $customerProductCategory): array;

    public function getCustomerProductCategoryIdsByCustomerProduct(CustomerProduct $customerProduct): array;

    public function setProductValues(CustomerProduct $customerProduct, array $customerProductCategoryIds, bool $value): void;
}
