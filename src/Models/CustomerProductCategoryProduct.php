<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProductCategoryProduct extends Model
{
    protected $fillable = [
        'customer_product_id',
        'customer_product_category_id',
    ];

    public $timestamps = false;
}
