<?php

namespace Molitor\CustomerProduct\Console\Commands;

use Molitor\Customer\Models\Customer;
use Molitor\CustomerProduct\Repositories\CustomerProductRepository;
use Illuminate\Console\Command;

class CopyCustomerProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:customer-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ügyfél termékek átmásolása a termék törzsbe';

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
        $customerProductRepository = new CustomerProductRepository();

        foreach (Customer::get() as $customer) {
            $customerProductRepository->copyNextCustomerProduct($customer);
        }

        return 0;
    }
}
