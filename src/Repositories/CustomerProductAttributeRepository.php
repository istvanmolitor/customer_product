<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductAttribute;
use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductField;
use Molitor\Product\Models\ProductFieldOption;
use Molitor\Product\Models\ProductFieldValue;
use Molitor\Product\Repositories\ProductFieldOptionRepositoryInterface;
use Molitor\Product\Repositories\ProductFieldRepositoryInterface;

class CustomerProductAttributeRepository implements CustomerProductAttributeRepositoryInterface
{
    private CustomerProductAttribute $customerProductFieldValue;

    public function __construct(
        private ProductFieldRepositoryInterface $productFieldRepository,
        private ProductFieldOptionRepositoryInterface $productFieldOptionRepository
    )
    {
        $this->customerProductFieldValue = new CustomerProductAttribute();
    }

    public function setValue(CustomerProduct $customerProduct, string $name, $value, string $language = 'hu'): void
    {
        $field = $this->productFieldRepository->findOrCreate($name, $language);

        if ($field->multiple && is_array($value)) {
            $this->deleteProductValues($customerProduct);
            foreach ($value as $valueElement) {
                $this->add($customerProduct, $this->productFieldOptionRepository->findOrCreate($field, $valueElement));
            }
        } else {
            $this->deleteFieldValues($customerProduct, $field)
                ->add($customerProduct, $this->productFieldOptionRepository->findOrCreate($field, $value));
        }
    }

    public function getInfo(CustomerProduct $customerProduct): mixed
    {
        $values = $this->customerProductFieldValue
            ->where('customer_product_id', $customerProduct->id)
            ->join(
                'product_field_options',
                'product_field_options.id',
                '=',
                'customer_product_field_values.product_field_option_id'
            )
            ->join('product_fields', 'product_fields.id', '=', 'product_field_options.product_field_id')
            ->join('languages', 'languages.id', '=', 'product_fields.language_id')
            ->orderBy('product_fields.name')
            ->orderBy('product_field_options.name')
            ->select(
                [
                    'languages.code AS language',
                    'product_fields.multiple AS multiple',
                    'product_fields.name AS name',
                    'product_field_options.name AS value',
                ]
            )
            ->get();

        $info = [];
        foreach ($values as $value) {
            if ($value->multiple) {
                if (!isset($info[$value->name])) {
                    $info[$value->language][$value->name] = [];
                }
                $info[$value->language][$value->name][] = $value->value;
            } else {
                $info[$value->language][$value->name] = $value->value;
            }
        }

        return $info;
    }

    public function deleteProductValues(CustomerProduct $customerProduct): self
    {
        $this->customerProductFieldValue
            ->where('customer_product_id', $customerProduct->id)
            ->delete();

        return $this;
    }

    protected function exists(CustomerProduct $customerProduct, ProductFieldOption $productFieldOption): bool
    {
        return $this->customerProductFieldValue
                ->where('customer_product_id', $customerProduct->id)
                ->where('product_field_option_id', $productFieldOption->id)
                ->count() > 0;
    }

    protected function deleteFieldValues(CustomerProduct $customerProduct, ProductField $productField): self
    {
        $this->customerProductFieldValue
            ->join(
                'product_field_options',
                'product_field_options.id',
                '=',
                'customer_product_field_values.product_field_option_id'
            )
            ->where('customer_product_field_values.customer_product_id', $customerProduct->id)
            ->where('product_field_options.product_field_id', $productField->id)
            ->delete();

        return $this;
    }

    private function add(CustomerProduct $customerProduct, ProductFieldOption $productFieldOption): self
    {
        if (!$this->exists($customerProduct, $productFieldOption)) {
            $this->customerProductFieldValue->create(
                [
                    'customer_product_id' => $customerProduct->id,
                    'product_field_option_id' => $productFieldOption->id,
                ]
            );
        }
        return $this;
    }

    public function overwrite(CustomerProduct $customerProduct, Product $product): self
    {
        return $this->merge($customerProduct, $product);
    }

    public function merge(CustomerProduct $customerProduct, Product $product): self
    {
        $customerProductOptionIds = $customerProduct->customerProductFieldValues()->pluck(
            'product_field_option_id'
        )->toArray();
        $productOptionIds = $product->productFieldValues()->pluck('product_field_option_id')->toArray();

        foreach (array_diff($customerProductOptionIds, $productOptionIds) as $optionId) {
            CustomerProductAttribute::create(
                [
                    'customer_product_id' => $product->id,
                    'product_field_option_id' => $optionId,
                ]
            );
        }

        return $this;
    }
}
