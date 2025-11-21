@php
    /** @var \Molitor\Customer\Models\Customer $customer */
    /** @var \Illuminate\Support\Collection|\Molitor\CustomerProduct\Models\CustomerProductCategory[] $categories */
    $customer = $data['customer'] ?? null;
    $categories = $data['categories'] ?? collect();
@endphp

<x-filament::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ $customer?->name }} – Termékkategóriák</h1>

            <div class="flex gap-2">
                <x-filament::button
                    tag="a"
                    :href="\Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource::getUrl('index', ['customer_id' => $customer->id])"
                    icon="heroicon-o-list-bullet"
                    color="gray"
                    outlined
                >
                    Lista nézet
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="\Molitor\CustomerProduct\Filament\Resources\CustomerProductCategoryResource::getUrl('create', ['customer_id' => $customer->id])"
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
