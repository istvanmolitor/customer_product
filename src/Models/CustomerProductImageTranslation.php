<?php

namespace Molitor\CustomerProduct\Models;

use Molitor\Language\Models\TranslationModel;

class CustomerProductImageTranslation extends TranslationModel
{
    public function getTranslatableModelClass(): string
    {
        return CustomerProductImage::class;
    }

    public function getTranslationForeignKey(): string
    {
        return 'customer_product_image_id';
    }

    public function getTranslatableFields(): array
    {
        return [
            'title',
            'alt',
        ];
    }
}
