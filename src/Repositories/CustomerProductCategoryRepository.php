<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Models\CustomerProductCategory as Category;

class CustomerProductCategoryRepository implements CustomerProductCategoryRepositoryInterface
{
    private Category $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function getRootCategoryByName(Customer $customer, string $name, int|string|null $language = null): ?Category
    {
        return $this->category
            ->joinTranslation($language)
            ->where('customer_id', $customer->id)
            ->where('parent_id', 0)
            ->whereTranslation('name', $name)
            ->baseSelect()
            ->first();
    }

    public function createRootCategory(Customer $customer, string $name, int|string|null $language = null): Category
    {
        $category = $this->getRootCategoryByName($customer, $name, $language);
        if ($category) {
            return $category;
        }

        $category = new CustomerProductCategory();
        $category->customer_id = $category->id;
        $category->parent_id = 0;
        $category->setAttributeTranslation('name', $name, $language);
        $category->save();
        return $category;
    }

    public function getSubCategoryByName(Category $parent, string $name, int|string|null $language = null): Category|null
    {
        return $this->category
            ->joinTranslation($language)
            ->where('customer_id', $parent->customer_id)
            ->where('parent_id', $parent->id)
            ->whereTranslation('name', $name)
            ->baseSelect()
            ->first();
    }

    public function createSubCategory(Category $parent, string $name, int|string|null $language = null): Category
    {
        $category = $this->getSubCategoryByName($parent, $name, $language);
        if ($category) {
            return $category;
        }

        $category = new CustomerProductCategory();
        $category->customer_id = $parent->customer_id;
        $category->parent_id = $parent->id;
        $category->setAttributeTranslation('name', $name, $language);
        $category->save();
        return $category;
    }

    public function getPathCategories(Category $category): array
    {
        $path = $category->parent ? $this->getPathCategories($category->parent) : [];
        $path[] = $category;
        return $path;
    }

    private array $pathCache = [];

    public function getPath(Category $category, int|string|null $language = null): array
    {
        if (!array_key_exists($category->id, $this->pathCache)) {
            $path = [];
            foreach ($this->getPathCategories($category) as $pathCategory) {
                $path[] = $pathCategory->getAttributeTranslation('name', $language);
            }
            $this->pathCache[$category->id] = $path;
        }
        return $this->pathCache[$category->id];
    }

    public function getCategoryToString(Category $category, $separator = '/', int|string|null $language = null): string
    {
        return implode($separator, $this->getPath($category, $language));
    }

    public function createCategory(Customer $customer, array $path, int|string|null $language = null): Category|null
    {
        $parent = null;
        foreach ($path as $name) {
            if ($parent === null) {
                $parent = $this->createRootCategory($customer, $name, $language);
            } else {
                $parent = $this->createSubCategory($parent, $name, $language);
            }
        }
        return $parent;
    }

    public function getRootCategories(Customer $customer): Collection
    {
        return $this->category
            ->where('customer_id', $customer->id)
            ->where('parent_id', 0)
            ->baseSelect()
            ->get();
    }

    public function deleteProductCategories(Customer $customer): void
    {
        foreach ($this->getRootCategories($customer) as $category) {
            $this->deleteProductCategory($category);
        }
    }

    public function deleteProductCategory(CustomerProductCategory $customerProductCategory): self
    {
        /** @var CustomerProductCategoryProductRepositoryInterface $customerProductCategoryProductRepository */
        $customerProductCategoryProductRepository = app(CustomerProductCategoryProductRepositoryInterface::class);
        foreach ($customerProductCategory->childCategories as $childCategory) {
            $this->deleteProductCategory($childCategory);

            $customerProductCategoryProductRepository->deleteByCustomerProductCategory($customerProductCategory);
        }

        $customerProductCategory->delete();
        return $this;
    }

    public function getAllByCustomer(Customer $customer): Collection
    {
        return $this->category->where('customer_id', $customer->id)->orderBy('name')->get();
    }

    public function getByIds(Customer $customer, array $customerProductCategoryIds): Collection
    {
        return $this->category
            ->where('customer_id', $customer->id)
            ->whereIn('id', $customerProductCategoryIds)
            ->get();
    }
}
