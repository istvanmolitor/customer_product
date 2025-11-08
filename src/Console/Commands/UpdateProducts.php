<?php

namespace Molitor\CustomerProduct\Console\Commands;

use Illuminate\Console\Command;
use Molitor\CustomerProduct\Repositories\ProductImageRepository;
use Molitor\CustomerProduct\Repositories\ProductRepository;

class UpdateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Frissíti a termékek adatait az ügyféltermékek alapján';

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
        $productRepository = new ProductRepository();

        $products = $productRepository->getAll();

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products as $product) {
            $productRepository->setMinPrice($product);
            $progressBar->advance();
        }

        $progressBar->finish();

        return 0;
    }
}
