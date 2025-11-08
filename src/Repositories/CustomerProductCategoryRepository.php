<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Models\CustomerProductCategory as Category;

class CustomerProductCategoryRepository implements CustomerProductCategoryRepositoryInterface
{
    private Category $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function getRootCategoryByName(Customer $customer, string $name): ?Category
    {
        return $this->category
            ->where('customer_id', $customer->id)
            ->where('parent_id', 0)
            ->where('name', $name)
            ->first();
    }

    public function getPathCategories(Category $category): array
    {
        $path = $category->parent ? $this->getPathCategories($category->parent) : [];
        $path[] = $category;
        return $path;
    }

    private array $pathCache = [];

    public function getPath(Category $category): array
    {
        if (!array_key_exists($category->id, $this->pathCache)) {
            $path = [];
            foreach ($this->getPathCategories($category) as $pathCategory) {
                $path[] = $pathCategory->name;
            }
            $this->pathCache[$category->id] = $path;
        }
        return $this->pathCache[$category->id];
    }

    public function getCategoryToString(Category $category, $separator = '/'): string
    {
        return implode($separator, $this->getPath($category));
    }

    public function createRootCategory(Customer $customer, string $name): Category
    {
        $category = $this->getRootCategoryByName($customer, $name);
        if ($category) {
            return $category;
        }
        return $this->category->create(
            [
                'customer_id' => $customer->id,
                'parent_id' => 0,
                'name' => $name,
            ]
        );
    }

    public function getSubCategoryByName(Category $parent, string $name): ?Category
    {
        return $this->category
            ->where('customer_id', $parent->customer_id)
            ->where('parent_id', $parent->id)
            ->where('name', $name)
            ->first();
    }

    public function createSubCategory(Category $parent, string $name): Category
    {
        $category = $this->getSubCategoryByName($parent, $name);
        if ($category) {
            return $category;
        }
        return $this->category->create(
            [
                'customer_id' => $parent->customer_id,
                'parent_id' => $parent->id,
                'name' => $name,
            ]
        );
    }

    public function createCategory(Customer $customer, array $path): ?Category
    {
        $parent = null;
        foreach ($path as $name) {
            if ($parent === null) {
                $parent = $this->createRootCategory($customer, $name);
            } else {
                $parent = $this->createSubCategory($parent, $name);
            }
        }
        return $parent;
    }

    public function addProductToCategory(Category $category, CustomerProduct $customerProduct): self
    {
        (new CustomerProductCategoryProductRepository())->setValue($category, $customerProduct, true);
        return $this;
    }

    public function getRootCategories(Customer $customer)
    {
        return $this->category
            ->where('customer_id', $customer->id)
            ->where('parent_id', 0)
            ->orderBy('name')
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
        $customerProductCategoryProductRepository = new CustomerProductCategoryProductRepository();
        foreach ($customerProductCategory->childCategories as $childCategory) {
            $this->deleteProductCategory($childCategory);

            $customerProductCategoryProductRepository->deleteByCustomerProductCategory($customerProductCategory);

            $this->deleteImage($customerProductCategory);
        }

        $customerProductCategory->delete();
        return $this;
    }

    public function deleteImage(CustomerProductCategory $customerProductCategory): void
    {
        if ($customerProductCategory->file_id) {
            (new FileRepository())->deleteFile($customerProductCategory->file);
            $customerProductCategory->file_id = null;
            $customerProductCategory->save();
        }
    }

    public function downloadImage(CustomerProductCategory $customerProductCategory): void
    {
        $url = $customerProductCategory->image_url;
        if (!empty($url) && $customerProductCategory->file_id === null) {
            $file = (new FileRepository())->storeUrl($url);
            if ($file) {
                $customerProductCategory->file_id = $file->id;
                $customerProductCategory->save();
            }
        }
    }

    public function getCustomerProductCategoryWithImageUrl(Customer $customer): Collection
    {
        return $this->category
            ->where('customer_id', $customer->id)
            ->whereNotNull('image_url')
            ->whereNull('file_id')
            ->get();
    }

    public function getAllByCustomer(Customer $customer): Collection
    {
        return $this->category->where('customer_id', $customer->id)->orderBy('name')->get();
    }

    public function getByIds(Customer $customer, array $customerProductCategoryIds): ?Category
    {
        return $this->category
            ->where('customer_id', $customer->id)
            ->whereIn('id', $customerProductCategoryIds)
            ->orderBy('name')
            ->get();
    }
}
