<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Molitor\Customer\Models\Customer;
use Molitor\Language\Models\TranslatableModel;

class CustomerProductCategory extends TranslatableModel
{
    protected $fillable = [
        'customer_id',
        'parent_id',
        'left_value',
        'right_value',
        'url',
        'image_url',
    ];

    public function getTranslationModelClass(): string
    {
        return CustomerProductCategoryTranslation::class;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function childCategories(): HasMany
    {
        return $this->hasMany(CustomerProductCategory::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CustomerProductCategory::class, 'parent_id');
    }

    public function customerProducts(): HasMany
    {
        return $this->hasMany(CustomerProduct::class, 'parent_id');
    }

    public function __toString(): string
    {
        return $this->name ?? "Kategória #{$this->id}";
    }
}
