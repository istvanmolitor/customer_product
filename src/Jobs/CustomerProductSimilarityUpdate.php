<?php

namespace Molitor\CustomerProduct\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Services\SimilarityService;

class CustomerProductSimilarityUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $customerProductId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $customerProductId)
    {
        $this->customerProductId = $customerProductId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SimilarityService $similarityService)
    {
        $customerProduct = CustomerProduct::find($this->customerProductId);
        if ($customerProduct) {
            $similarityService->updateCustomerProduct($customerProduct, true);
        }
    }
}

