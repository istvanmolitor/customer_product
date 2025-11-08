<?php

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Support\LazyCollection;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductSimilarity;
use Molitor\Product\Models\Product;

interface CustomerProductSimilarityRepositoryInterface
{
    public function getByProduct(Product $product): LazyCollection;

    public function delete(CustomerProductSimilarity $customerProductSimilarity): void;

    public function deleteRecord(?Product $product, ?CustomerProduct $customerProduct1, ?CustomerProduct $customerProduct2): void;

    public function getMaxSimilarityByCustomerProduct(CustomerProduct $customerProduct): ?CustomerProductSimilarity;

    public function getSimilar(): ?CustomerProductSimilarity;

    public function deleteByProduct(Product $product): void;

    public function deleteByCustomerProduct(CustomerProduct $customerProduct): void;

    public function updateProduct(Product $product): void;

    public function updateCustomerProduct(CustomerProduct $customerProduct): void;

    public function insert(array $rows): int;
}
