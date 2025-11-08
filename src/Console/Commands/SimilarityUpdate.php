<?php

namespace Molitor\CustomerProduct\Console\Commands;

use Illuminate\Console\Command;
use Molitor\CustomerProduct\Repositories\CustomerProductSimilarityRepositoryInterface;

class SimilarityUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'similarity:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hasonló termékek keresése';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private CustomerProductSimilarityRepositoryInterface $customerProductSimilarityRepository,
    )
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
        $this->customerProductSimilarityRepository->updateAll();
        return 0;
    }
}
