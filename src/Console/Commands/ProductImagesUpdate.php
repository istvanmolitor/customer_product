<?php

namespace Molitor\CustomerProduct\Console\Commands;

use Illuminate\Console\Command;
use Molitor\CustomerProduct\Repositories\ProductImageRepository;
use Molitor\Product\Repositories\ProductRepository;

class ProductImagesUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-images:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Termékképek frissítése a ügyfél termékképei alapján';

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
        $productImageRepository = new ProductImageRepository();

        $products = (new ProductRepository())->getAll();

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products as $product) {
            $productImageRepository->update($product);
            $progressBar->advance();
        }

        $progressBar->finish();

        return 0;
    }
}
