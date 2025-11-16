<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductResource\Pages;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Customer\Models\Customer;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductResource;
use Molitor\CustomerProduct\Repositories\CustomerProductRepositoryInterface;

class ListCustomerProducts extends ListRecords
{
    protected static string $resource = CustomerProductResource::class;

    public Customer|null $cusomer = null;

    public function mount(): void
    {
        parent::mount();

        $customerId = request()->integer('customer_id');
        if(!$customerId) {
            abort(404);
        }

        $this->cusomer = app(CustomerRepositoryInterface::class)->getById($customerId);
        if(!$this->cusomer) {
            abort(404);
        }
    }

    public function getBreadcrumb(): string
    {
        return __('customer_product::common.list');
    }

    public function getTitle(): string
    {
        return __('customer_product::customer.list_title',  ['customer' => $this->cusomer->name]);
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
        $table = CustomerProductResource::table($table)
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

        $table->modifyQueryUsing(function ($query) {
            $query->where('customer_id', $this->cusomer->id);
        });

        return $table;
    }
}
