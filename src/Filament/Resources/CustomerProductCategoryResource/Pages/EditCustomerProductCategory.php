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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure customer_id cannot be changed during edit
        if (isset($this->record) && isset($this->record->customer_id)) {
            $data['customer_id'] = $this->record->customer_id;
        }

        return $data;
    }
}
