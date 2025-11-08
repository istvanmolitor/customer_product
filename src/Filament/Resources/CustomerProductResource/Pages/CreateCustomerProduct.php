<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductResource;
use Molitor\CustomerProduct\Models\CustomerProductAttribute;

class CreateCustomerProduct extends CreateRecord
{
    protected static string $resource = CustomerProductResource::class;

    protected function afterCreate(): void
    {
        $rows = $this->data['product_attributes_form'] ?? [];
        if (!is_array($rows)) {
            return;
        }

        $seen = [];
        foreach ($rows as $row) {
            $optionId = $row['product_field_option_id'] ?? null;
            if (!empty($optionId) && !isset($seen[$optionId])) {
                $seen[$optionId] = true;
                CustomerProductAttribute::create([
                    'customer_product_id' => $this->record->id,
                    'product_field_option_id' => $optionId,
                    'sort' => $row['sort'] ?? 0,
                ]);
            }
        }
    }
}
