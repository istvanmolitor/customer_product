<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource;

class ListCustomerProductCategories extends ListRecords
{
    protected static string $resource = CustomerProductCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return __('customer_product::common.list');
    }

    public function getTitle(): string
    {
        return __('customer_product::product_category.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('customer_product::product_category.create'))
                ->icon('heroicon-o-plus'),
        ];
    }
}
