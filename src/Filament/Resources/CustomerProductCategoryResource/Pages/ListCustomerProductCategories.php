<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource;

class ListCustomerProductCategories extends ListRecords
{
    protected static string $resource = CustomerProductCategoryResource::class;

    public int|null $customerId = null;

    public function mount(): void
    {
        parent::mount();
        $this->customerId = request()->integer('customer_id');
    }

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
        if (!$this->customerId) {
            return [];
        }

        return [
            CreateAction::make()
                ->label(__('customer_product::product_category.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        $table = CustomerProductCategoryResource::table($table);

        if ($this->customerId) {
            $table->modifyQueryUsing(function ($query) {
                $query->where('customer_id', $this->customerId);
            });
        } else {
            $table->modifyQueryUsing(function ($query) {
                $query->whereRaw('1 = 0');
            })->emptyStateHeading(__('customer_product::common.customer_required'));
        }

        return $table;
    }
}
