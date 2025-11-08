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
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Filament\Resources\CustomerListResource\Pages;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;

class CustomerListResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationGroup(): string
    {
        return __('customer::customer.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('customer_product::customer.title');
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
                Tables\Columns\IconColumn::make('is_seller')
                    ->boolean()
                    ->label(__('customer::common.is_seller')),
                Tables\Columns\IconColumn::make('is_buyer')
                    ->boolean()
                    ->label(__('customer::common.is_buyer')),
                Tables\Columns\TextColumn::make('customerGroup.name')->label(__('customer::common.group'))->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                // No per-record actions for a read-only list in this package
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Keep UI consistent even if no bulk actions are provided
                    DeleteBulkAction::make()->visible(false),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $customerTable = (new Customer())->getTable();
        $cpTable = (new CustomerProduct())->getTable();
        $cpcTable = (new CustomerProductCategory())->getTable();

        return parent::getEloquentQuery()
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
