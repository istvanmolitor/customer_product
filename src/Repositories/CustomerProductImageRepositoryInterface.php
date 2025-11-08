<?php

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductImage;
use Molitor\Product\Models\Product;

interface CustomerProductImageRepositoryInterface
{
    public function getNextSort(CustomerProduct $customerProduct): int;

    public function getUrls(CustomerProduct $customerProduct): array;

    public function updateUrls(CustomerProduct $customerProduct, array $urls): void;

    public function getByUrls(CustomerProduct $customerProduct, array $urls): Collection;

    public function delete(CustomerProductImage $customerProductImage): void;

    public function deleteByUrls(CustomerProduct $customerProduct, array $urls): void;

    public function getImageByUrl(CustomerProduct $customerProduct, string $url): ?CustomerProductImage;

    public function insertUrl(CustomerProduct $customerProduct, string $url, string $title = null): CustomerProductImage;

    public function saveImage(CustomerProduct $customerProduct, string $url, string $title = null): CustomerProductImage;

    public function saveImages(CustomerProduct $customerProduct, array $urls): self;

    public function clearImages(CustomerProduct $customerProduct): self;

    public function deleteImage(CustomerProductImage $customerProductImage);

    public function setImages(CustomerProduct $customerProduct, array $urls): self;

    public function update(CustomerProduct $customerProduct, Product $product): self;

    public function merge(CustomerProduct $customerProduct, Product $product): self;

    public function downloadImage(CustomerProductImage $customerProductImage): void;

    public function getUrl(?CustomerProductImage $customerProductImage): ?string;

    public function getCountByCustomerProduct($customerProduct): int;
}
