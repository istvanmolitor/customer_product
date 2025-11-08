<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Molitor\Currency\Models\Currency;
use Molitor\Customer\Models\Customer;
use Molitor\Language\Models\TranslatableModel;
use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductUnit;

class CustomerProduct extends TranslatableModel
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'same_customer_product',
        'sku',
        'url',
        'price',
        'currency_id',
        'stock',
        'product_unit_id',
    ];

    public function getTranslationModelClass(): string
    {
        return CustomerProductTranslation::class;
    }

    public function __toString(): string
    {
        return $this->sku;
    }

    public function customerProductImages(): HasMany
    {
        return $this->hasMany(CustomerProductImage::class, 'customer_product_id', 'id');
    }

    public function customerProductImage(): HasOne
    {
        return $this->hasOne(CustomerProductImage::class, 'customer_product_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function customerProductCategories(): BelongsToMany
    {
        return $this->belongsToMany(CustomerProductCategory::class, 'customer_product_category_products','customer_product_id', 'customer_product_category_id');
    }

    public function customerProductFieldValues(): HasMany
    {
        return $this->hasMany(CustomerProductAttribute::class, 'customer_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
