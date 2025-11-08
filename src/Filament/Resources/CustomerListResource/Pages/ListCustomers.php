<?php

namespace Molitor\CustomerProduct\Filament\Resources\CustomerListResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Molitor\CustomerProduct\Filament\Resources\CustomerListResource;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerListResource::class;

    public function getBreadcrumb(): string
    {
        return __('customer::common.list');
    }

    public function getTitle(): string
    {
        return __('customer::customer.title');
    }

    protected function getHeaderActions(): array
    {
        // Read-only list in this package
        return [];
    }
}
