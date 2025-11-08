<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\Product\Models\Product;

interface CustomerProductAttributeRepositoryInterface
{
    public function setValue(CustomerProduct $customerProduct, string $name, $value, string $language = 'hu'): void;

    public function getInfo(CustomerProduct $customerProduct): mixed;

    public function deleteProductValues(CustomerProduct $customerProduct): self;

    public function overwrite(CustomerProduct $customerProduct, Product $product): self;

    public function merge(CustomerProduct $customerProduct, Product $product): self;
}
