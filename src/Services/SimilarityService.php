<?php

namespace Molitor\CustomerProduct\Services;

use Molitor\CustomerProduct\Jobs\CustomerProductSimilarityUpdate;
use Molitor\CustomerProduct\Jobs\ProductSimilarityUpdate;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Repositories\CustomerProductRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductSimilarityRepositoryInterface;
use Molitor\Product\Models\Product;

class SimilarityService
{
    private array $rows = [];

    public function __construct(
        private CustomerProductRepositoryInterface           $customerProductRepository,
        private CustomerProductSimilarityRepositoryInterface $customerProductSimilarityRepository
    )
    {

    }

    /*Product*********************************************************************************************/

    public function setProductKeywords(Product $product, array|string $keywords): void
    {
        if (is_array($keywords)) {
            $keywords = $this->arrayToString($keywords);
        } else {
            $keywords = $this->arrayToString($this->stringToArray($keywords));
        }
        $oldKeywords = (string)$product->keywords;
        if ($keywords !== $oldKeywords) {
            $product->keywords = $keywords;
            $product->save();
            $this->updateProduct($product);
        }
    }

    public function updateProduct(Product $product, $immediately = false): void
    {
        if ($immediately) {
            $this->deleteByProduct($product);
            $this->addProductRows($product);
            $this->storeRows();
        } else {
            ProductSimilarityUpdate::dispatch($product->id);
        }
    }

    protected function addProductRows(Product $product): void
    {
        foreach ($this->customerProductRepository->getNotJointCustomerProducts($product) as $customerProduct) {
            $this->addProductRow($product, $customerProduct);
        }
    }

    protected function addProductRow(Product $product, CustomerProduct $customerProduct): void
    {
        $similarity = $this->getProductSimilarity($product, $customerProduct);
        if ($similarity > 0) {
            $this->rows[] = [
                'product_id' => $product->id,
                'customer_product_1_id' => $customerProduct->id,
                'customer_product_2_id' => 0,
                'similarity' => $similarity,
            ];
        }
    }

    public function getProductSimilarity(Product $product, CustomerProduct $customerProduct): float
    {
        return $this->cosineSimilarity(
            $this->getProductKeywords($product),
            $this->getCustomerProductKeywords($customerProduct)
        );
    }

    public function getProductKeywords(Product $product): array
    {
        return $this->stringToArray($product->keywords);
    }

    public function deleteByProduct(Product $product): void
    {
        $this->customerProductSimilarityRepository->deleteByProduct($product);
    }

    /*Customer product*********************************************************************************************/

    public function setCustomerProductKeywords(CustomerProduct $customerProduct, array|string $keywords): void
    {
        if (is_array($keywords)) {
            $keywords = $this->arrayToString($keywords);
        } else {
            $keywords = $this->arrayToString($this->stringToArray($keywords));
        }
        $oldKeywords = (string)$customerProduct->keywords;
        if ($keywords !== $oldKeywords) {
            $customerProduct->keywords = $keywords;
            $customerProduct->save();
            $this->updateCustomerProduct($customerProduct);
        }
    }

    public function updateCustomerProduct(CustomerProduct $customerProduct, $immediately = false): void
    {
        if ($immediately) {
            $this->deleteByCustomerProduct($customerProduct);
            $this->addCustomerProductRows($customerProduct);
            $this->storeRows();
        } else {
            CustomerProductSimilarityUpdate::dispatch($customerProduct->id);
        }
    }

    protected function addCustomerProductRows(CustomerProduct $customerProduct): void
    {
        foreach ($this->customerProductRepository->getOtherCustomerProducts($customerProduct) as $customerProduct2) {
            $this->addCustomerProductRow($customerProduct, $customerProduct2);
        }
    }

    protected function addCustomerProductRow(CustomerProduct $customerProduct1, CustomerProduct $customerProduct2): void
    {
        $similarity = $this->getCustomerProductSimilarity($customerProduct1, $customerProduct2);
        if ($similarity > 0) {
            $this->rows[] = [
                'product_id' => 0,
                'customer_product_1_id' => $customerProduct1->id,
                'customer_product_2_id' => $customerProduct2->id,
                'similarity' => $similarity,
            ];
        }
    }

    public function getCustomerProductSimilarity(CustomerProduct $customerProduct1, CustomerProduct $customerProduct2): float
    {
        return $this->cosineSimilarity(
            $this->getCustomerProductKeywords($customerProduct1),
            $this->getCustomerProductKeywords($customerProduct2)
        );
    }

    protected function getCustomerProductKeywords(CustomerProduct $customerProduct): array
    {
        return $this->stringToArray($customerProduct->keywords);
    }

    public function deleteByCustomerProduct(CustomerProduct $customerProduct): void
    {
        $this->customerProductSimilarityRepository->deleteByCustomerProduct($customerProduct);
    }

    /********************************************************************************/

    protected function stringToArray(string $string): array
    {
        if ($string === '') {
            return [];
        }
        return explode(",", $string);
    }

    protected function arrayToString(array $array): string
    {
        if (count($array)) {
            $array = array_filter($array, function ($item) {
                return !empty($item);
            });
            sort($array);
            return implode(",", $array);
        }
        return '';
    }

    protected function storeRows(): void
    {
        $this->customerProductSimilarityRepository->insert($this->rows);
        $this->rows = [];
    }

    public function cosineSimilarity(array $vector1, array $vector2): ?float
    {
        $vector2 = array_fill_keys($vector2, 1);

        $prod = 0.0;
        $v1Norm = 0.0;

        foreach (array_fill_keys($vector1, 1) as $i => $xi) {
            if (isset($vector2[$i])) {
                $prod += $xi * $vector2[$i];
            }
            $v1Norm += $xi * $xi;
        }
        $v1Norm = sqrt($v1Norm);
        if ($v1Norm == 0) {
            return null;
        }

        $v2Norm = 0.0;
        foreach ($vector2 as $xi) {
            $v2Norm += $xi * $xi;
        }
        $v2Norm = sqrt($v2Norm);
        if ($v2Norm == 0) {
            return null;
        }

        return $prod / ($v1Norm * $v2Norm);
    }

    public function updateAll(): void
    {
        foreach ($this->customerProductRepository->getAll() as $customerProduct) {
            $this->updateCustomerProduct($customerProduct);
        }
    }
}
