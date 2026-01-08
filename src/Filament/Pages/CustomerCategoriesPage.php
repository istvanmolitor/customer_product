<?php

namespace Molitor\CustomerProduct\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use Molitor\Customer\Repositories\CustomerRepositoryInterface;
use Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepositoryInterface;

class CustomerCategoriesPage extends Page
{
    protected string $view = 'customer_product::filament.pages.customer-categories';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-list-bullet';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $customerId = null;
    public $categories = [];

    public static function canAccess(): bool
    {
        return Gate::allows('acl', 'customer_product');
    }

    public function getTitle(): string|Htmlable
    {
        if ($this->customerId) {
            /** @var CustomerRepositoryInterface $customerRepository */
            $customerRepository = app(CustomerRepositoryInterface::class);
            $customer = $customerRepository->getById($this->customerId);
            if ($customer) {
                return $customer->name . ' – Termékkategóriái';
            }
        }
        return 'Ügyfél termékkategóriái';
    }


    public function mount(): void
    {
        $customerId = request()->integer('customer_id');
        if (!$customerId) {
            abort(404);
        }

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = app(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById($customerId);
        if (!$customer) {
            abort(404);
        }

        /** @var CustomerProductCategoryRepositoryInterface $categoryRepository */
        $categoryRepository = app(CustomerProductCategoryRepositoryInterface::class);
        $categories = $categoryRepository->getRootCategories($customer);

        $this->customerId = $customer->id;
        $this->categories = $categories;
    }


    public function editCategory(int $categoryId): void
    {
        $this->redirect(
            CustomerProductCategoryResource::getUrl(
                'edit',
                ['record' => $categoryId, 'customer_id' => $this->customerId]
            )
        );
    }

    public function deleteCategory(int $categoryId): void
    {
        try {
            /** @var CustomerRepositoryInterface $customerRepository */
            $customerRepository = app(CustomerRepositoryInterface::class);
            $customer = $customerRepository->getById($this->customerId);

            if (!$customer) {
                \Filament\Notifications\Notification::make()
                    ->title('Hiba')
                    ->body('Az ügyfél nem található.')
                    ->danger()
                    ->send();
                return;
            }

            /** @var CustomerProductCategoryRepositoryInterface $categoryRepository */
            $categoryRepository = app(CustomerProductCategoryRepositoryInterface::class);

            // Find the category
            $categories = $categoryRepository->getAllByCustomer($customer);
            $category = $categories->firstWhere('id', $categoryId);

            if (!$category) {
                \Filament\Notifications\Notification::make()
                    ->title('Hiba')
                    ->body('A kategória nem található.')
                    ->danger()
                    ->send();
                return;
            }

            $categoryName = $category->name;

            // Delete the category
            $categoryRepository->deleteProductCategory($category);

            // Refresh the categories
            $this->categories = $categoryRepository->getRootCategories($customer);

            \Filament\Notifications\Notification::make()
                ->title('Sikeres törlés')
                ->body("A(z) \"{$categoryName}\" kategória és minden alkategóriája sikeresen törölve.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Hiba')
                ->body('A kategória törlése sikertelen: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
