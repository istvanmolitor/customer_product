<?php

namespace Molitor\CustomerProduct\Models;

use Illuminate\Database\Eloquent\Model;
use Molitor\Product\Models\Product;

class CustomerProductSimilarity extends Model
{
    protected $table = 'customer_product_similarities';

    protected $fillable = [
        'product_id',
        'customer_product_1_id',
        'customer_product_2_id',
        'similarity',
    ];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function customerProduct1()
    {
        return $this->belongsTo(CustomerProduct::class, 'customer_product_1_id');
    }

    public function customerProduct2()
    {
        return $this->belongsTo(CustomerProduct::class, 'customer_product_2_id');
    }
}
