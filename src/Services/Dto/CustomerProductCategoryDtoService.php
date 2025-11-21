<?php

namespace Molitor\CustomerProduct\Services\Dto;

use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepositoryInterface;
use Molitor\Product\Dto\ProductCategoryDto;

class CustomerProductCategoryDtoService
{
    public function __construct(
        protected CustomerProductCategoryRepositoryInterface $customerProductCategoryRepository
    )
    {
    }

    public function makeDto(CustomerProductCategory $category): ProductCategoryDto
    {
        $categoryDto = new ProductCategoryDto();

        $categories = $this->customerProductCategoryRepository->getPathCategories($category);
        foreach ($categories as $category) {
            $categoryDto->path->addProductCategory($category->getAttributeDto('name'));
        }

        $categoryDto->id = $category->id;
        $categoryDto->source = 'customer_product_category';
        $categoryDto->description = $category->getAttributeDto('description');
        return $categoryDto;
    }

    public function saveDto(ProductCategoryDto $categoryDto): CustomerProductCategory
    {

    }
}
