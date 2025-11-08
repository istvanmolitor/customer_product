<?php

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Support\LazyCollection;
use Molitor\CustomerProduct\Jobs\ProductSimilarityUpdate;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductSimilarity;
use Molitor\Product\Models\Product;
use Molitor\Product\Repositories\ProductRepositoryInterface;

class CustomerProductSimilarityRepository implements CustomerProductSimilarityRepositoryInterface
{
    private CustomerProductSimilarity $customerProductSimilarity;

    public function __construct(
        private ProductRepositoryInterface         $productRepository,
        private CustomerProductRepositoryInterface $customerProductRepository
    )
    {
        $this->customerProductSimilarity = new CustomerProductSimilarity();
    }

    public function getByProduct(Product $product): LazyCollection
    {
        return $this->customerProductSimilarity
            ->where('product_id', $product->id)
            ->with(['product', 'customerProduct1', 'customerProduct2'])
            ->cursor();
    }

    public function delete(CustomerProductSimilarity $customerProductSimilarity): void
    {
        $this->customerProductSimilarity
            ->where('product_id', $customerProductSimilarity->product_id)
            ->where('customer_product_1_id', $customerProductSimilarity->customer_product_1_id)
            ->where('customer_product_2_id', $customerProductSimilarity->customer_product_2_id)
            ->delete();
    }

    private function getRecord(?Product $product, ?CustomerProduct $customerProduct1, ?CustomerProduct $customerProduct2): ?CustomerProductSimilarity
    {
        if ($product) {
            $customerProduct = $customerProduct1 ?: $customerProduct2;

            return $this->customerProductSimilarity
                ->where('product_id', $product->id)
                ->where('customer_product_1_id', $customerProduct->id)
                ->where('customer_product_2_id', 0)
                ->first();
        } else {
            return $this->customerProductSimilarity
                ->where('product_id', 0)
                ->where(function ($query) use ($customerProduct1, $customerProduct2) {
                    $query->where(function ($a) use ($customerProduct1, $customerProduct2) {
                        $a->where('customer_product_1_id', $customerProduct1->id)
                            ->where('customer_product_2_id', $customerProduct2->id);
                    })->orWhere(function ($b) use ($customerProduct1, $customerProduct2) {
                        $b->where('customer_product_2_id', $customerProduct1->id)
                            ->where('customer_product_1_id', $customerProduct2->id);
                    });
                })
                ->first();
        }
    }

    public function deleteRecord(?Product $product, ?CustomerProduct $customerProduct1, ?CustomerProduct $customerProduct2): void
    {
        $record = $this->getRecord($product, $customerProduct1, $customerProduct2);
        if ($record) {
            $this->delete($record);
        }
    }

    public function getMaxSimilarityByCustomerProduct(CustomerProduct $customerProduct): ?CustomerProductSimilarity
    {
        return $this->customerProductSimilarity
            ->where(function ($query) use ($customerProduct) {
                $query->where('customer_product_1_id', $customerProduct->id)
                    ->orWhere('customer_product_2_id', $customerProduct->id);
            })
            ->with(['product', 'customerProduct1', 'customerProduct2'])
            ->orderBy('similarity', 'desc')
            ->first();
    }

    private function getByCustomerProduct($customerProduct): LazyCollection
    {
        return $this->customerProductSimilarity
            ->where(function ($query) use ($customerProduct) {
                $query->where('customer_product_1_id', $customerProduct->id)
                    ->orWhere('customer_product_2_id', $customerProduct->id);
            })
            ->with(['product', 'customerProduct1', 'customerProduct2'])
            ->cursor();
    }

    private function createCustomerProduct(CustomerProduct $customerProduct): int
    {
        if ($customerProduct->product_id) {
            return 0;
        }

        $customerProductKeywords = $this->customerProductRepository->getAllKeyword($customerProduct);

        /** @var CustomerProduct $otherCustomerProduct */
        $data = [];
        foreach ($this->customerProductRepository->getOtherCustomerProducts($customerProduct) as $otherCustomerProduct) {
            $similarity = $customerProductKeywords->cosineSimilarity($this->customerProductRepository->getAllKeyword($otherCustomerProduct));
            if ($similarity > 0) {
                $data[] = [
                    'product_id' => 0,
                    'customer_product_1_id' => $customerProduct->id,
                    'customer_product_2_id' => $otherCustomerProduct->id,
                    'similarity' => $similarity,
                ];
            }
        }

        $productIds = $this->customerProductRepository->getProductIdsByCustomer($customerProduct->customer);

        /** @var Product $product */
        foreach ($this->productRepository->getAll() as $product) {
            if (!in_array($product->id, $productIds)) {
                $similarity = $customerProductKeywords->cosineSimilarity($this->productRepository->getAllKeyword($product));
                if ($similarity > 0) {
                    $data[] = [
                        'product_id' => $product->id,
                        'customer_product_1_id' => $customerProduct->id,
                        'customer_product_2_id' => 0,
                        'similarity' => $similarity,
                    ];
                }
            }
        }

        return CustomerProductSimilarity::insertOrIgnore($data);
    }

    private function createProduct(Product $product): int
    {
        $productKeywords = $this->productRepository->getAllKeyword($product);

        $data = [];

        /** @var CustomerProduct $customerProduct */
        foreach ($this->customerProductRepository->getNotJointCustomerProducts($product) as $customerProduct) {
            $similarity = $productKeywords->cosineSimilarity($this->customerProductRepository->getKeywords($customerProduct));
            if ($similarity > 0) {
                $data[] = [
                    'product_id' => $product->id,
                    'customer_product_1_id' => $customerProduct->id,
                    'customer_product_2_id' => 0,
                    'similarity' => $similarity,
                ];
            }
        }

        return CustomerProductSimilarity::insertOrIgnore($data);
    }

    /**
     * Visszaad egy sort a leghasolóbb termékkel. Ami még nincs összekötve
     * @return CustomerProductSimilarity|null
     */
    public function getSimilar(): ?CustomerProductSimilarity
    {
        //Legjobb termékkel megeggyező aminek a cikkszáma is megeggyezik
        $similarProduct = $this->customerProductSimilarity
            ->where('customer_product_similarities.product_id', '<>', 0)
            ->where('customer_product_similarities.customer_product_2_id', 0)
            ->join('products', 'products.id', '=', 'customer_product_similarities.product_id')
            ->join('customer_products', 'customer_products.id', '=', 'customer_product_similarities.customer_product_1_id')
            ->whereRaw('products.sku = customer_products.sku')
            ->orderBy('customer_product_similarities.similarity', 'desc')
            ->select('customer_product_similarities.*')
            ->first();

        if ($similarProduct) {
            return $similarProduct;
        }

        $similarProduct = $this->customerProductSimilarity
            ->where('customer_product_similarities.product_id', 0)
            ->where('customer_product_similarities.customer_product_2_id', '<>', 0)
            ->join('customer_products AS cp1', 'cp1.id', '=', 'customer_product_similarities.customer_product_1_id')
            ->join('customer_products AS cp2', 'cp2.id', '=', 'customer_product_similarities.customer_product_2_id')
            ->whereRaw('cp1.sku = cp2.sku')
            ->orderBy('customer_product_similarities.similarity', 'desc')
            ->select('customer_product_similarities.*')
            ->first();

        if ($similarProduct) {
            return $similarProduct;
        }

        return $this->customerProductSimilarity
            ->orderBy('similarity', 'desc')
            ->first();
    }

    public function deleteByProduct(Product $product): void
    {
        $this->customerProductSimilarity->where('product_id', $product->id)->delete();
    }

    public function deleteByCustomerProduct(CustomerProduct $customerProduct): void
    {
        $this->customerProductSimilarity->where(function ($query) use ($customerProduct) {
            $query->where('customer_product_1_id', $customerProduct->id)
                ->orWhere('customer_product_2_id', $customerProduct->id);
        })->delete();
    }

    public function updateProduct(Product $product): void
    {
        $this->deleteByProduct($product);
        $this->createProduct($product);
    }

    public function updateCustomerProduct(CustomerProduct $customerProduct): void
    {
        $this->deleteByCustomerProduct($customerProduct);
        $this->createCustomerProduct($customerProduct);
    }

    public function insert(array $rows): int
    {
        return CustomerProductSimilarity::insertOrIgnore($rows);
    }
}
