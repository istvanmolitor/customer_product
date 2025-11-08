<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Molitor\Product\Models\ProductFieldOption;

class CustomerProductAttribute extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_product_id',
        'product_field_option_id',
        'sort',
    ];

    public function customerProduct(): BelongsTo
    {
        return $this->belongsTo(CustomerProduct::class, 'customer_product_id');
    }

    public function productFieldOption(): BelongsTo
    {
        return $this->belongsTo(ProductFieldOption::class, 'product_field_option_id');
    }
}
