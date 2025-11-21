<?php

namespace Molitor\CustomerProduct\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Filament\Resources\CustomerListResource\Pages;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductResource;
use Molitor\CustomerProduct\Filament\Pages\CustomerCategoriesPage;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;

class CustomerListResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-tag';

    public static function getNavigationGroup(): string
    {
        return __('customer::customer.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('customer_product::customer.navigation_label');
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'customer_product');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('customer::common.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer_products_count')
                    ->label(__('customer_product::common.customer_products_count'))
                    ->sortable()
                    ->url(fn ($record) => CustomerProductResource::getUrl('index') . '?customer_id=' . $record->id),
                Tables\Columns\TextColumn::make('categories_count')
                    ->label(__('customer_product::common.categories_count'))
                    ->sortable()
                    ->url(fn ($record) => CustomerCategoriesPage::getUrl() . '?customer_id=' . $record->id),
            ])
            ->filters([
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $customerTable = (new Customer())->getTable();
        $cpTable = (new CustomerProduct())->getTable();
        $cpcTable = (new CustomerProductCategory())->getTable();

        return parent::getEloquentQuery()
            ->select([$customerTable . '.*'])
            ->selectSub(function ($sub) use ($customerTable, $cpTable) {
                $sub->from($cpTable . ' as cp')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('cp.customer_id', $customerTable . '.id');
            }, 'customer_products_count')
            ->selectSub(function ($sub) use ($customerTable, $cpcTable) {
                $sub->from($cpcTable . ' as cpc')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('cpc.customer_id', $customerTable . '.id');
            }, 'categories_count')
            ->where(function (Builder $query) use ($customerTable, $cpTable, $cpcTable) {
                $query
                    ->whereExists(function ($sub) use ($customerTable, $cpTable) {
                        $sub->selectRaw('1')
                            ->from($cpTable . ' as cp')
                            ->whereColumn('cp.customer_id', $customerTable . '.id');
                    })
                    ->orWhereExists(function ($sub) use ($customerTable, $cpcTable) {
                        $sub->selectRaw('1')
                            ->from($cpcTable . ' as cpc')
                            ->whereColumn('cpc.customer_id', $customerTable . '.id');
                    });
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
        ];
    }
}
