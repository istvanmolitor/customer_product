<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Models;

use Molitor\Customer\Models\Customer;
use Molitor\Language\Models\TranslatableModel;

class CustomerProductCategory extends TranslatableModel
{
    protected $fillable = [
        'customer_id',
        'parent_id',
        'url',
        'image_url',
    ];

    public function getTranslationModelClass(): string
    {
        return CustomerProductCategoryTranslation::class;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function childCategories()
    {
        return $this->hasMany(CustomerProductCategory::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(CustomerProductCategory::class, 'parent_id');
    }

    public function customerProducts()
    {
        return $this->hasMany(CustomerProduct::class, 'customer_product_category_id');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function __toString()
    {
        return $this->name;
    }
}
