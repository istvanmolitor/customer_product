<?php

namespace Molitor\CustomerProduct\database\seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Molitor\Currency\Models\Currency;
use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductCategory;
use Molitor\CustomerProduct\Repositories\CustomerProductCategoryRepositoryInterface;
use Molitor\Language\Repositories\LanguageRepositoryInterface;
use Molitor\Product\Models\Product;
use Molitor\Product\Models\ProductUnit;
use Molitor\User\Exceptions\PermissionException;
use Molitor\User\Services\AclManagementService;

class CustomerProductSeeder extends Seeder
{
    protected function createCategory(Customer $customer, $faker, $languages, int $parentId = 0)
    {
        $category = new CustomerProductCategory();
        $category->customer_id = $customer->id;
        $category->parent_id = $parentId;
        $category->url = $faker->optional()->url();
        $category->image_url = $faker->optional()->imageUrl(640, 480, 'technics', true);
        foreach ($languages as $language) {
            $name = ucfirst($faker->word());
            $category->setAttributeTranslation('name', $name, $language->code);
            $category->setAttributeTranslation('description', $faker->sentence(10), $language->code);
            $category->setAttributeTranslation('keywords', implode(',', $faker->words(5)), $language->code);
        }
        $category->save();
        return $category;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Register permissions
        try {
            /** @var AclManagementService $aclService */
            $aclService = app(AclManagementService::class);
            $aclService->createPermission('customer_product', 'Termékek kezelése', 'admin');
        } catch (PermissionException $e) {
            $this->command->error($e->getMessage());
        }

        if (!app()->isLocal()) {
            return;
        }

        $faker = Faker::create('hu_HU');

        // Base data dependencies
        $customer = Customer::query()->inRandomOrder()->first();
        $currency = Currency::query()->inRandomOrder()->first();
        $unit = ProductUnit::query()->inRandomOrder()->first();
        $languages = app(LanguageRepositoryInterface::class)->getEnabledLanguages();



        if (!$customer || !$currency || !$unit || $languages->isEmpty()) {
            $this->command?->warn('CustomerProductSeeder skipped fake data: missing base data (customer, currency, unit, or languages).');
            return;
        }

        $categories = collect();
        for ($i = 0; $i < 5; $i++) {
            $category = $this->createCategory($customer, $faker, $languages);
            $categories->push($category);
            for ($j = 0; $j < 3; $j++) {
                $subCategory = $this->createCategory($customer, $faker, $languages, $customer->id);
                $categories->push($subCategory);
                for ($k = 0; $k < 2; $k++) {
                    $subSubCategory = $this->createCategory($customer, $faker, $languages, $subCategory->id);
                    $categories->push($subSubCategory);
                }
            }
        }

        /** @var CustomerProductCategoryRepositoryInterface $customerProductCategoryRepository */
        $customerProductCategoryRepository = app(CustomerProductCategoryRepositoryInterface::class);
        $customerProductCategoryRepository->refreshLeftRight();

        // Create some customer products
        $productCount = 15;
        for ($i = 0; $i < $productCount; $i++) {
            $cp = new CustomerProduct();
            $cp->customer_id = $customer->id;
            $cp->product_id = Product::query()->inRandomOrder()->value('id'); // may be null
            $cp->same_customer_product = null;
            $cp->sku = strtoupper('CP-' . $faker->bothify('????-#####'));
            $cp->url = $faker->optional()->url();
            $cp->price = $faker->optional()->randomFloat(2, 1000, 50000);
            $cp->currency_id = $currency->id;
            $cp->stock = $faker->numberBetween(0, 500);
            $cp->product_unit_id = $unit->id;

            foreach ($languages as $language) {
                $name = ucfirst($faker->words(3, true));
                $cp->setAttributeTranslation('name', $name, $language->code);
                $cp->setAttributeTranslation('description', $faker->paragraph(2), $language->code);
                $cp->setAttributeTranslation('keywords', implode(',', $faker->words(6)), $language->code);
            }

            $cp->save();

            // Attach 1-3 random categories
            $attachIds = $categories->random($faker->numberBetween(1, min(3, $categories->count())))->pluck('id')->all();
            $cp->customerProductCategories()->attach($attachIds);
        }
    }
}
