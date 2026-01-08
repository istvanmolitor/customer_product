@php
    /** @var int|null $customerId */
    /** @var \Illuminate\Support\Collection $categories */
    $customerRepository = app(\Molitor\Customer\Repositories\CustomerRepositoryInterface::class);
    $customer = $customerId ? $customerRepository->getById($customerId) : null;
@endphp

<x-filament::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ $customer?->name }} – Termékkategóriák</h1>

            <div class="flex gap-2">
                <x-filament::button
                    tag="a"
                    :href="\Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource::getUrl('index', ['customer_id' => $customerId])"
                    icon="heroicon-o-list-bullet"
                    color="gray"
                    outlined
                >
                    Lista nézet
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="\Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource::getUrl('create', ['customer_id' => $customerId])"
                    icon="heroicon-o-plus"
                    color="primary"
                >
                    Új kategória létrehozása
                </x-filament::button>
            </div>
        </div>

        @if($categories->isEmpty())
            <p class="text-gray-500">Nincs megjeleníthető kategória.</p>
        @else
            @include('customer_product::filament.pages.sub-categories', ['categories' => $categories])
        @endif
    </div>
</x-filament::page>
