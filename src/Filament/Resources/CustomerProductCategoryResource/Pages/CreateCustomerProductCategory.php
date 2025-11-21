<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource;

class CreateCustomerProductCategory extends CreateRecord
{
    protected static string $resource = CustomerProductCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $customerId = request()->integer('customer_id');
        if ($customerId) {
            $data['customer_id'] = $customerId;
        }

        return $data;
    }
}
