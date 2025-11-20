<?php

namespace Molitor\CustomerProduct\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages\CreateCustomerProductCategory;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages\EditCustomerProductCategory;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource\Pages\ListCustomerProductCategories;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\Language\Filament\Components\TranslatableFields;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepositoryInterface;

class CustomerProductCategoryResource extends Resource
{
    protected static ?string $model = CustomerProductCategory::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'customer_product');
    }

    public static function form(Schema $schema): Schema
    {
        /** @var CustomerProductCategoryRepositoryInterface $categoryRepository */
        $categoryRepository = app(CustomerProductCategoryRepositoryInterface::class);

        // Resolve customer from URL query parameter and fetch categories accordingly
        $options = collect();
        $customerId = request()->integer('customer_id');
        if ($customerId) {
            /** @var CustomerRepositoryInterface $customerRepository */
            $customerRepository = app(CustomerRepositoryInterface::class);
            $customer = $customerRepository->getById($customerId);
            if ($customer) {
                $options = $categoryRepository->getAllByCustomer($customer)->pluck('name', 'id');
            }
        }

        return $schema->components([
            Forms\Components\Select::make('parent_id')
                ->label(__('customer_product::common.parent_category'))
                ->options($options)
                ->default(0)
                ->required(),
            Forms\Components\FileUpload::make('image')
                ->label(__('customer_product::common.product_category_image'))
                ->image()
                ->disk('public')
                ->directory('product-categories')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->nullable()
                ->preserveFilenames(false)
                ->getUploadedFileNameForStorageUsing(fn(\Illuminate\Http\UploadedFile $file): string => time() . '_' . $file->hashName()),
            TranslatableFields::schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('customer_product::common.name'))
                    ->required()
                    ->maxLength(255),
            ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('customer_product::common.id'))
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('customer_product::common.image'))
                    ->size(100)
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('customer_product::common.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('customer_product::common.parent'))
                    ->toggleable(),
            ])
            ->filters([])
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

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerProductCategories::route('/'),
        'create' => CreateCustomerProductCategory::route('/create'),
        'edit' => EditCustomerProductCategory::route('/{record}/edit'),
        ];
    }
}
