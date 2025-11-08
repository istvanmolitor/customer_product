<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Models\CustomerProductCategory as Category;
use Molitor\Product\Libs\Keywords;

interface CustomerProductCategoryRepositoryInterface
{
    public function getRootCategoryByName(Customer $customer, string $name): ?Category;

    public function getPathCategories(Category $category): array;

    public function getPath(Category $category): array;

    public function getCategoryToString(Category $category, $separator = '/'): string;

    public function createRootCategory(Customer $customer, string $name): Category;

    public function getSubCategoryByName(Category $parent, string $name): ?Category;

    public function createSubCategory(Category $parent, string $name): Category;

    public function createCategory(Customer $customer, array $path): ?Category;

    public function addProductToCategory(Category $category, CustomerProduct $customerProduct): self;

    public function getRootCategories(Customer $customer);

    public function deleteProductCategories(Customer $customer): void;

    public function deleteProductCategory(Category $customerProductCategory): self;

    public function deleteImage(Category $customerProductCategory): void;

    public function downloadImage(Category $customerProductCategory): void;

    public function getCustomerProductCategoryWithImageUrl(Customer $customer): Collection;

    public function getAllByCustomer(Customer $customer): Collection;

    public function getByIds(Customer $customer, array $customerProductCategoryIds): ?CustomerProductCategory;
}
