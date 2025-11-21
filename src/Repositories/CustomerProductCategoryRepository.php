<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProductCategory;

class CustomerProductCategoryRepository implements CustomerProductCategoryRepositoryInterface
{
    private CustomerProductCategory $category;

    public function __construct()
    {
        $this->category = new CustomerProductCategory();
    }

    public function getRootCategoryByName(Customer $customer, string $name, int|string|null $language = null): ?CustomerProductCategory
    {
        return $this->category
            ->joinTranslation($language)
            ->where('customer_id', $customer->id)
            ->where('parent_id', 0)
            ->whereTranslation('name', $name)
            ->selectBase()
            ->first();
    }

    public function createRootCategory(Customer $customer, string $name, int|string|null $language = null): CustomerProductCategory
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

    public function getSubCategoryByName(CustomerProductCategory $parent, string $name, int|string|null $language = null): CustomerProductCategory|null
    {
        return $this->category
            ->joinTranslation($language)
            ->where('customer_id', $parent->customer_id)
            ->where('parent_id', $parent->id)
            ->whereTranslation('name', $name)
            ->selectBase()
            ->first();
    }

    public function createSubCategory(CustomerProductCategory $parent, string $name, int|string|null $language = null): CustomerProductCategory
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

    public function getPathCategories(CustomerProductCategory $category): array
    {
        $path = $category->parent ? $this->getPathCategories($category->parent) : [];
        $path[] = $category;
        return $path;
    }

    private array $pathCache = [];

    public function getPath(CustomerProductCategory $category, int|string|null $language = null): array
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

    public function getCategoryToString(CustomerProductCategory $category, $separator = '/', int|string|null $language = null): string
    {
        return implode($separator, $this->getPath($category, $language));
    }

    public function createCategory(Customer $customer, array $path, int|string|null $language = null): CustomerProductCategory|null
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

    public function getRootCategories(Customer $customer, int|string|null $language = null): Collection
    {
        return $this->category
            ->joinTranslation($language)
            ->where('customer_id', $customer->id)
            ->where('parent_id', 0)
            ->selectBase()
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

    public function getAllByCustomer(Customer $customer, int|string|null $language = null): Collection
    {
        return $this->category->where('customer_id', $customer->id)->joinTranslation($language)->orderByTranslation('name')->get();
    }

    public function getByIds(Customer $customer, array $customerProductCategoryIds): Collection
    {
        return $this->category
            ->where('customer_id', $customer->id)
            ->whereIn('id', $customerProductCategoryIds)
            ->get();
    }

    public function refreshLeftRight(): void
    {
        $this->refreshLeftRightValue(0, 1);
    }

    private function refreshLeftRightValue(int $productCategoryId, int $leftValue): int
    {
        $subCategories = $this->category->where('parent_id', $productCategoryId)->get();
        if ($subCategories->count() === 0) {
            if ($productCategoryId !== 0) {
                $this->category->where('id', $productCategoryId)->update([
                    'left_value' => $leftValue,
                    'right_value' => $leftValue,
                ]);
            }
            return $leftValue;
        } else {
            foreach ($subCategories as $subCategory) {
                $subCategory->left_value = $leftValue;
                $rightValue = $this->refreshLeftRightValue($subCategory->id, $leftValue);
                $subCategory->right_value = $rightValue;
                $subCategory->save();

                $leftValue = $rightValue + 1;
            }
            return $rightValue;
        }
    }
}
