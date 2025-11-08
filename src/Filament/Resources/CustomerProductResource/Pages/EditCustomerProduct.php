<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductResource;
use Molitor\CustomerProduct\Models\CustomerProductAttribute;

class EditCustomerProduct extends EditRecord
{
    protected static string $resource = CustomerProductResource::class;

    public function getTitle(): string
    {
        return __('customer_product::product.edit');
    }

    public function getBreadcrumb(): string
    {
        return __('customer_product::common.edit');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $attributes = CustomerProductAttribute::query()
            ->where('customer_product_id', $this->record->id)
            ->with('productFieldOption')
            ->get();

        $data['product_attributes_form'] = $attributes->map(function (CustomerProductAttribute $value) {
            return [
                'product_field_id' => optional($value->productFieldOption)->product_field_id,
                'product_field_option_id' => $value->product_field_option_id,
                'sort' => $value->sort,
            ];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        CustomerProductAttribute::query()->where('customer_product_id', $this->record->id)->delete();

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
