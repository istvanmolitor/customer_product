<?php

namespace Molitor\CustomerProduct\Services\Dto;

use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Models\CustomerProductImage;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryProductRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductImageRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductRepositoryInterface;
use Molitor\Product\Dto\ImageDto;
use Molitor\Product\Dto\ProductCategoryDto;
use Molitor\Product\Dto\ProductDto;
use Molitor\Product\Services\Dto\BaseProductDtoService;
use Molitor\Product\Services\Dto\ProductUnitDtoService;

class CustomerProductDtoService extends BaseProductDtoService
{
    public function __construct(
        protected CustomerProductRepositoryInterface $customerProductRepository,
        protected ProductUnitDtoService $productUnitDtoService,
        protected CurrencyRepositoryInterface $currencyRepository,
        protected CustomerProductImageRepositoryInterface $customerProductImageRepository,
        protected CustomerProductCategoryProductRepositoryInterface $customerProductCategoryProductRepository
    )
    {
    }

    public function saveDto(Customer $customer, ProductDto $productDto): CustomerProduct
    {
        $customerProduct = $this->makeModel($customer, $productDto);
        $this->fillCustomerProduct($customerProduct, $productDto);
        $customerProduct->save();

        $this->updateCustomerProductImages($customerProduct, $productDto);
        $this->updateCustomerProductCategories($customerProduct, $productDto);

        return $customerProduct;
    }

    public function makeModel(Customer $customer, ProductDto $productDto): CustomerProduct
    {
        if($productDto->source === 'customer_product' and $productDto->id)
        {
            $customerProduct = $this->customerProductRepository->getById($productDto->id);
            if($customerProduct) {
                return $customerProduct;
            }
        }
        $customerProduct = $this->customerProductRepository->getBySku($customer, $productDto->sku);
        if($customerProduct) {
            return $customerProduct;
        }
        $customerProduct = new CustomerProduct();
        $customerProduct->customer_id = $customer->id;
        $customerProduct->sku = $productDto->sku;
        return $customerProduct;
    }

    public function fillCustomerProduct(CustomerProduct $customerProduct, ProductDto $productDto): void
    {
        $customerProduct->sku = $productDto->sku;
        $customerProduct->setAttributeDto('name', $productDto->name);
        $customerProduct->setAttributeDto('description', $productDto->description);
        $customerProduct->price = $productDto->price;
        $customerProduct->url = $productDto->url;
        $customerProduct->product_unit_id = $this->productUnitDtoService->saveDto($productDto->productUnit)->id;
        $customerProduct->currency_id = $this->currencyRepository->getByCode($productDto->currency)?->id;
    }

    public function updateCustomerProductImages(CustomerProduct $customerProduct, ProductDto $productDto): void
    {
        $this->updateModelImages($customerProduct, 'customerProductImages', 'customer_product_id', $productDto);
        $imageDtos = [];
        /** @var ImageDto $imageDto */
        foreach ($productDto->getImages() as $imageDto) {
            $imageDtos[$imageDto->url] = $imageDto;
        }

        $images = [];
        /** @var CustomerProductImage $customerProductImage */
        foreach ($customerProduct->customerProductImages as $customerProductImage) {
            $images[$customerProductImage->url] = $customerProductImage;
        }

        $i = 0;
        /**
         * @var string $url
         * @var ImageDto $imageDto
         */
        foreach ($imageDtos as $url => $imageDto) {
            if (isset($images[$url])) {
                $customerProductImage = $images[$url];
            }
            else {
                $customerProductImage =  new CustomerProductImage();
                $customerProductImage->customer_product_id = $customerProduct->id;
            }

            $customerProductImage->url = $imageDto->url;
            $customerProductImage->title = $imageDto->title;
            $customerProductImage->alt = $imageDto->alt;
            $customerProductImage->sort = $i++;
            $customerProductImage->save();
        }

        /**
         * @var string $url
         * @var CustomerProductImage  $customerProductImage
         */
        foreach ($images as $url => $customerProductImage) {
            if(!isset($imageDtos[$url])) {
                $customerProductImage->delete();
            }
        }
    }

    public function updateCustomerProductCategories(CustomerProduct $customerProduct, ProductDto $productDto): void
    {
        $this->customerProductCategoryProductRepository->deleteByCustomerProduct($customerProduct);
        /** @var ProductCategoryDto $category */
        foreach ($productDto->getCategories() as $category)
        {
            //$this->customerProductCategoryProductRepository->setValue($customerProduct);
        }
    }
}
