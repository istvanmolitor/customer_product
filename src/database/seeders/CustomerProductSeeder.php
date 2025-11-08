<?php

namespace Molitor\CustomerProduct\database\seeders;

use Illuminate\Database\Seeder;
use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductField;
use Molitor\Product\Models\ProductFieldOption;
use Molitor\Product\Models\ProductUnit;
use Molitor\Product\Models\ProductUnitTranslation;
use Molitor\User\Exceptions\PermissionException;
use Molitor\User\Services\AclManagementService;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class CustomerProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            /** @var AclManagementService $aclService */
            $aclService = app(AclManagementService::class);
            $aclService->createPermission('customer_product', 'Termékek kezelése', 'admin');
        } catch (PermissionException $e) {
            $this->command->error($e->getMessage());
        }
    }
}
