<?php

namespace Molitor\CustomerProduct\Console\Commands;

use Illuminate\Console\Command;

class CustomerProductCategoryImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer-categories:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ügyfél kategória képeinek letöltése';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*
        $repository = new CustomerProductCategoryRepository();

        $shops = Shop::where('enabled', 1)->get();
        foreach ($shops as $shop) {
            $customerProductCategories = $shop->shopProductCategories()->whereNotNull('image_url')->whereNull(
                'file_id'
            )->get();
            foreach ($customerProductCategories as $customerProductCategory) {
                $repository->downloadImage($customerProductCategory);
                $this->info($customerProductCategory->image_url);
            }
        }
        */

        return 0;
    }
}
