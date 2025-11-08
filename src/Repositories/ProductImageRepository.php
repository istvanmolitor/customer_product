<?php

namespace Molitor\CustomerProduct\Repositories;

use Molitor\Product\Models\Product;

class ProductImageRepository extends \Molitor\Product\Repositories\ProductImageRepository
{
    protected CustomerProductRepository $customerProductRepository;

    protected CustomerProductImageRepository $customerProductImageRepository;

    public function __construct()
    {
        $this->customerProductRepository = new CustomerProductRepository();
        $this->customerProductImageRepository = new CustomerProductImageRepository();
        parent::__construct();
    }

    public function update(Product $product): void
    {
        $maxCount = null;
        $maxCountCustomerProduct = null;

        $customerProducts = $this->customerProductRepository->getByProduct($product);

        foreach ($customerProducts as $customerProduct) {
            $count = $this->customerProductImageRepository->getCountByCustomerProduct($customerProduct);
            if ($count > 0 && ($maxCount === null || $count > $maxCount)) {
                $maxCount = $count;
                $maxCountCustomerProduct = $customerProduct;
            }
        }

        if($maxCountCustomerProduct) {
            $this->updateUrls($product, $this->customerProductImageRepository->getUrls($maxCountCustomerProduct));
        }
    }
}
