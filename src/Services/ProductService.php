<?php

namespace Molitor\CustomerProduct\Services;

use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Services\Dto\CustomerProductDtoService;
use Molitor\Product\Models\Product;
use Molitor\Product\Services\Dto\ProductDtoService;
use Molitor\Unas\Models\UnasProduct;

class ProductService
{
    public function __construct(
        protected CustomerProductDtoService $customerProductDtoService,
        protected ProductDtoService $productDtoService
    )
    {
    }

    public function copyToProduct(CustomerProduct $customerProduct): Product
    {
        $productDto = $this->customerProductDtoService->makeDto($customerProduct);
        $product = $this->productDtoService->saveDto($productDto);
        $customerProduct->product_id = $product->id;
        $customerProduct->save();
        return $product;
    }
}
