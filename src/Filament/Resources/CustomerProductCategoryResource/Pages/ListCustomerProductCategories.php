<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Molitor\Customer\Models\Customer;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\CustomerProduct\Filament\Pages\CustomerCategoriesPage;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductResource;

class ListCustomerProductCategories extends ListRecords
{
    protected static string $resource = CustomerProductCategoryResource::class;

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
        return __('customer_product::product_category.list_title',  ['customer' => $this->cusomer->name]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('products')
                ->label(__('customer_product::common.title'))
                ->color('gray')
                ->url(fn () => CustomerProductResource::getUrl('index', ['customer_id' => $this->cusomer->id]))
                ->outlined(),
            Action::make('tree_view')
                ->label('Fa nézet')
                ->icon('heroicon-o-rectangle-group')
                ->color('gray')
                ->url(fn () => CustomerCategoriesPage::getUrl(['customer_id' => $this->cusomer->id]))
                ->outlined(),
            CreateAction::make()
                ->label(__('customer_product::product_category.create'))
                ->icon('heroicon-o-plus')
                ->url(fn () => CustomerProductCategoryResource::getUrl('create', ['customer_id' => $this->cusomer->id])),
        ];
    }

    public function table(Table $table): Table
    {
        $table = CustomerProductCategoryResource::table($table);

        $table->modifyQueryUsing(function ($query) {
            $query->where('customer_id', $this->cusomer->id);
        });

        return $table;
    }
}
