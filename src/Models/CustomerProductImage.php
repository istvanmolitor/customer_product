<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Models;

use Illuminate\Database\Eloquent\Model;
use Molitor\File\Models\File;
use Molitor\Language\Models\TranslatableModel;

class CustomerProductImage extends TranslatableModel
{
    protected $fillable = [
        'customer_product_id',
        'url',
        'sort',
        'title',
        'alt',
    ];

    public function getTranslationModelClass(): string
    {
        return CustomerProductImageTranslation::class;
    }

    public function customerProduct()
    {
        return $this->belongsTo(CustomerProduct::class, 'customer_product_id');
    }
}
