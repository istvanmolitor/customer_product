<?php

namespace Molitor\CustomerProduct\Services\Dto;

use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Repositories\CustomerProductRepositoryInterface;
use Molitor\Product\Dto\ProductDto;
use Molitor\Product\Services\Dto\ProductUnitDtoService;

class CustomerProductDtoService
{
    public function __construct(
        protected CustomerProductRepositoryInterface $customerProductRepository,
        protected ProductUnitDtoService $productUnitDtoService,
        protected CurrencyRepositoryInterface $currencyRepository
    )
    {
    }

    public function saveDto(Customer $customer, ProductDto $productDto): CustomerProduct
    {
        $customerProduct = $this->makeModel($customer, $productDto);
        $this->fillModel($customerProduct, $productDto);
        $customerProduct->save();

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

    public function fillModel(CustomerProduct $customerProduct, ProductDto $productDto): void
    {
        $customerProduct->sku = $productDto->sku;
        $customerProduct->setAttributeDto('name', $productDto->name);
        $customerProduct->setAttributeDto('description', $productDto->description);
        $customerProduct->price = $productDto->price;
        $customerProduct->url = $productDto->url;
        $customerProduct->product_unit_id = $this->productUnitDtoService->saveDto($productDto->productUnit)->id;
        $customerProduct->currency_id = $this->currencyRepository->getByCode($productDto->currency)?->id;
    }
}
