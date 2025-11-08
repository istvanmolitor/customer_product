<?php

namespace Molitor\CustomerProduct\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Molitor\CustomerProduct\Services\SimilarityService;
use Molitor\Product\Models\Product;

class ProductSimilarityUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $productId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SimilarityService $similarityService)
    {
        $product = Product::find($this->productId);
        if ($product) {
            $similarityService->updateProduct($product, true);
        }
    }
}

