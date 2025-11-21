<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Models\CustomerProductCategory as Category;

interface CustomerProductCategoryRepositoryInterface
{
    public function getRootCategoryByName(Customer $customer, string $name, int|string|null $language = null): Category|null;

    public function createRootCategory(Customer $customer, string $name, int|string|null $language = null): Category;

    public function getSubCategoryByName(Category $parent, string $name, int|string|null $language = null): Category|null;

    public function createSubCategory(Category $parent, string $name, int|string|null $language = null): Category;

    public function getPathCategories(Category $category): array;

    public function getPath(Category $category, int|string|null $language = null): array;

    public function getCategoryToString(Category $category, $separator = '/', int|string|null $language = null): string;

    public function createCategory(Customer $customer, array $path, int|string|null $language = null): Category|null;

    public function getRootCategories(Customer $customer, int|string|null $language = null): Collection;

    public function deleteProductCategories(Customer $customer): void;

    public function deleteProductCategory(Category $customerProductCategory): self;

    public function getAllByCustomer(Customer $customer, int|string|null $language = null): Collection;

    public function getByIds(Customer $customer, array $customerProductCategoryIds): Collection;

    public function refreshLeftRight(): void;
}
