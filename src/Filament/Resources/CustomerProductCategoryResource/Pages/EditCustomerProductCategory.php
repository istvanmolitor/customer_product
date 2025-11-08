<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource;

class EditCustomerProductCategory extends EditRecord
{
    protected static string $resource = CustomerProductCategoryResource::class;

    public function getTitle(): string
    {
        return __('customer_product::product_category.edit');
    }

    public function getBreadcrumb(): string
    {
        return __('customer_product::common.edit');
    }
}
