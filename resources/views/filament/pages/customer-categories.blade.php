@php
    /** @var \Molitor\Customer\Models\Customer $customer */
    /** @var \Illuminate\Support\Collection|\Molitor\CustomerProduct\Models\CustomerProductCategory[] $categories */
    $customer = $data['customer'] ?? null;
    $categories = $data['categories'] ?? collect();
@endphp

<x-filament::page>
    <div class="space-y-6">
        <h1 class="text-xl font-semibold">{{ $customer?->name }} – Termékkategóriák</h1>

        @if($categories->isEmpty())
            <p class="text-gray-500">Nincs megjeleníthető kategória.</p>
        @else
            @include('customer_product::filament.pages.sub-categories', ['categories' => $categories])
        @endif
    </div>
</x-filament::page>
