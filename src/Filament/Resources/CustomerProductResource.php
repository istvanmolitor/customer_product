<?php

namespace Molitor\CustomerProduct\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Molitor\Currency\Repositories\CurrencyRepository;
use Molitor\Currency\Repositories\CurrencyRepositoryInterface;
use Molitor\Language\Filament\Components\TranslatableFields;
use Molitor\Language\Repositories\LanguageRepository;
use Molitor\Language\Repositories\LanguageRepositoryInterface;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductResource\Pages;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductFieldOptionRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductFieldOptionRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductFieldRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductFieldRepositoryInterface;
use Molitor\CustomerProduct\Repositories\CustomerProductUnitRepository;
use Molitor\CustomerProduct\Repositories\CustomerProductUnitRepositoryInterface;
use Molitor\Product\Repositories\ProductFieldOptionRepositoryInterface;
use Molitor\Product\Repositories\ProductFieldRepositoryInterface;
use Molitor\Product\Repositories\ProductUnitRepositoryInterface;

class CustomerProductResource extends Resource
{
    protected static ?string $model = CustomerProduct::class;

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
        /** @var LanguageRepositoryInterface $languageRepository */
        $languageRepository = app(LanguageRepositoryInterface::class);

        /** @var ProductUnitRepositoryInterface $productUnitRepository */
        $productUnitRepository = app(ProductUnitRepositoryInterface::class);

        /** @var CurrencyRepositoryInterface $currencyRepository */
        $currencyRepository = app(CurrencyRepositoryInterface::class);

        /** @var CustomerProductCategoryRepositoryInterface $productCategoryRepository */
        $productCategoryRepository = app(CustomerProductCategoryRepositoryInterface::class);

        /** @var ProductFieldRepositoryInterface $productFieldRepository */
        $productFieldRepository = app(ProductFieldRepositoryInterface::class);

        /** @var ProductFieldOptionRepositoryInterface $productFieldOptionRepository */
        $productFieldOptionRepository = app(ProductFieldOptionRepositoryInterface::class);

        return $schema->components([
            Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make(__('customer_product::common.basic_data'))
                        ->schema([
                            Select::make('customer_id')
                                ->label(__('customer_product::common.customer'))
                                ->relationship('customer', 'name')
                                ->required()
                                ->searchable()
                                ->default(fn () => request()->integer('customer_id') ?: null)
                                ->reactive(),
                            Select::make('product_id')
                                ->label(__('customer_product::common.product'))
                                ->relationship('product', 'name')
                                ->searchable(),
                            Forms\Components\TextInput::make('sku')
                                ->label(__('customer_product::common.sku'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Forms\Components\TextInput::make('url')
                                ->label(__('customer_product::common.url'))
                                ->maxLength(255),
                            TranslatableFields::schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('customer_product::common.name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\RichEditor::make('description')
                                    ->label(__('customer_product::common.description'))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('keywords')
                                    ->label(__('customer_product::common.keywords'))
                                    ->maxLength(255),
                            ]),
                            Forms\Components\Select::make('customerProductCategories')
                                ->label(__('customer_product::common.categories'))
                                ->relationship('customerProductCategories', 'id')
                                ->multiple()
                                ->searchable()
                                ->preload(),
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('stock')
                                        ->label(__('customer_product::common.stock'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(99999999999),
                                    Forms\Components\Select::make('product_unit_id')
                                        ->label(__('customer_product::common.product_unit'))
                                        ->options($productUnitRepository->getOptions())
                                        ->default($productUnitRepository->getDefaultId())
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                            ]),
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('price')
                                        ->label(__('customer_product::common.price'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(99999999999),
                                    Forms\Components\Select::make('currency_id')
                                        ->label(__('customer_product::common.currency'))
                                        ->relationship('currency', 'code')
                                        ->default($currencyRepository->getDefaultId())
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ]),
                        ]),
                    Tabs\Tab::make(__('customer_product::common.attributes'))
                        ->schema([
                            Forms\Components\Repeater::make('product_attributes_form')
                                ->label(__('customer_product::common.product_attributes'))
                                ->dehydrated(false)
                                ->orderColumn('sort')
                                ->default([])
                                ->schema([
                                    Forms\Components\Select::make('product_field_id')
                                        ->label(__('customer_product::common.product_field'))
                                        ->options($productFieldRepository->getOptions())
                                        ->searchable()
                                        ->preload()
                                        ->reactive()
                                        ->required(),
                                    Forms\Components\Select::make('product_field_option_id')
                                        ->label(__('customer_product::common.product_field_option'))
                                        ->options(function ($get) use ($productFieldOptionRepository) {
                                            $fieldId = $get('product_field_id');
                                            if (!$fieldId) {
                                                return [];
                                            }
                                            return $productFieldOptionRepository->getOptionsByCustomerProductFieldId((int)$fieldId);
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->disabled(fn($get) => empty($get('product_field_id'))),
                                ])->columns(2),
                        ]),
                    Tabs\Tab::make(__('customer_product::common.product_images'))->schema([
                        Forms\Components\Repeater::make('customerProductImages')
                            ->label(__('customer_product::common.image_data'))
                            ->relationship('customerProductImages')
                            ->orderColumn('sort')
                            ->reorderable()
                            ->schema([
                                Forms\Components\Toggle::make('is_main')
                                    ->label(__('customer_product::common.main_image'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $productImages = $get('../../productImages') ?? [];
                                            foreach ($productImages as $index => $image) {
                                                if (array_key_exists('is_main', $image) && $image['is_main'] && $index != array_search($get(), $productImages)) {
                                                    $set("../../productImages.{$index}.is_main", false);
                                                }
                                            }
                                        }
                                    }),
                                Grid::make(3)->schema([
                                    Group::make([
                                        Forms\Components\TextInput::make('url')
                                            ->label(__('customer_product::common.image_url'))
                                            ->url()
                                            ->required(),
                                    ])->columnSpan(1)->gap(1),
                                    Group::make([
                                        Forms\Components\Repeater::make('translations')
                                            ->default(fn () => [
                                                ['language_id' => $languageRepository->getDefaultId()],
                                            ])
                                            ->label(__('customer_product::common.translations'))
                                            ->relationship('translations')
                                            ->schema([
                                                Forms\Components\Select::make('language_id')
                                                    ->label(__('customer_product::common.language'))
                                                    ->relationship(name: 'language', titleAttribute: 'code')
                                                    ->default($languageRepository->getDefaultId())
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Forms\Components\TextInput::make('title')
                                                    ->label(__('customer_product::common.image_title'))
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('alt')
                                                    ->label(__('customer_product::common.image_alt'))
                                                    ->maxLength(255),
                                            ])->columns(2),
                                    ])->columnSpan(2)->gap(2),
                                ]),
                            ])->columns(1),
                        ]),
                ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('customer_product::common.sku'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('translation.name')
                    ->label(__('customer_product::common.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('customer_product::common.price'))
                    ->formatStateUsing(fn ($record) => $record->price . ' ' . $record->currency->code)
                    ->sortable(),
            ])
            ->filters([
            ])
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
            'index' => Pages\ListCustomerProducts::route('/'),
            'create' => Pages\CreateCustomerProduct::route('/create'),
            'edit' => Pages\EditCustomerProduct::route('/{record}/edit'),
        ];
    }
}
