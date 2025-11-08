<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductResource\Pages;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductResource;

class ListCustomerProducts extends ListRecords
{
    protected static string $resource = CustomerProductResource::class;

    public function getBreadcrumb(): string
    {
        return __('customer_product::common.list');
    }

    public function getTitle(): string
    {
        return __('customer_product::product.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('customer_product::product.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        return CustomerProductResource::table($table)
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
