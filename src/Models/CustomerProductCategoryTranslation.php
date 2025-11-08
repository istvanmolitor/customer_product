<?php

namespace Molitor\CustomerProduct\Models;

use Molitor\Language\Models\TranslationModel;

class CustomerProductCategoryTranslation extends TranslationModel
{
    public function getTranslatableModelClass(): string
    {
        return CustomerProductCategory::class;
    }

    public function getTranslationForeignKey(): string
    {
        return 'customer_product_category_id';
    }

    public function getTranslatableFields(): array
    {
        return [
            'name',
            'description',
            'keywords',
        ];
    }
}
