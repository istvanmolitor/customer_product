<?php

declare(strict_types=1);

namespace Molitor\CustomerProduct\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\CustomerProduct\Models\CustomerProduct;
use Molitor\CustomerProduct\Models\CustomerProductImage;
use Molitor\File\Repositories\ImageFileRepositoryInterface;
use Molitor\Product\Models\Product;

class CustomerProductImageRepository implements CustomerProductImageRepositoryInterface
{
    private CustomerProductImage $customerProductImage;

    public function __construct(
        private ImageFileRepositoryInterface $imageFileRepository
    )
    {
        $this->customerProductImage = new CustomerProductImage();
    }

    public function getNextSort(CustomerProduct $customerProduct): int
    {
        return 1 + (int)$this->customerProductImage->where('customer_product_id', $customerProduct->id)->max('sort');
    }

    public function getUrls(CustomerProduct $customerProduct): array
    {
        return $this->customerProductImage->where('customer_product_id', $customerProduct->id)->orderBy('sort')->pluck('url')->toArray();
    }

    public function updateUrls(CustomerProduct $customerProduct, array $urls): void
    {
        $oldUrls = $this->getUrls($customerProduct);
        $insertUrls = array_diff($urls, $oldUrls);
        foreach ($insertUrls as $insertUrl) {
            $this->insertUrl($customerProduct, $insertUrl);
        }
        $this->deleteByUrls($customerProduct, array_diff($oldUrls, $urls));
    }

    public function getByUrls(CustomerProduct $customerProduct, array $urls): Collection
    {
        return $this->customerProductImage->where('customer_product_id', $customerProduct->id)->whereIn('url', $urls)->orderBy('sort')->get();
    }

    public function delete(CustomerProductImage $customerProductImage): void
    {
        $file = $customerProductImage->file;
        if ($file) {
            $this->imageFileRepository->deleteFile($file);
        }
        $customerProductImage->delete();
    }

    public function deleteByUrls(CustomerProduct $customerProduct, array $urls): void
    {
        /** @var CustomerProductImage $customerProductImage */
        foreach ($this->getByUrls($customerProduct, $urls) as $customerProductImage) {
            $this->delete($customerProductImage);
        }
    }

    public function getImageByUrl(CustomerProduct $customerProduct, string $url): ?CustomerProductImage
    {
        return $this->customerProductImage->where('customer_product_id', $customerProduct->id)
            ->where('url', $url)->first();
    }

    public function insertUrl(CustomerProduct $customerProduct, string $url, string $title = null): CustomerProductImage
    {
        return $this->customerProductImage->create(
            [
                'customer_product_id' => $customerProduct->id,
                'url' => $url,
                'sort' => $this->getNextSort($customerProduct),
                'title' => $title
            ]
        );
    }

    public function saveImage(CustomerProduct $customerProduct, string $url, string $title = null): CustomerProductImage
    {
        $customerProductImage = $this->getImageByUrl($customerProduct, $url);
        if (!$customerProductImage) {
            return $this->insertUrl($customerProduct, $url, $title);
        }

        if (!empty($title)) {
            $customerProductImage->title = $title;
            $customerProductImage->save();
        }
        return $customerProductImage;
    }

    public function saveImages(CustomerProduct $customerProduct, array $urls): self
    {
        foreach ($urls as $url) {
            $this->saveImage($customerProduct, $url);
        }
        return $this;
    }

    public function clearImages(CustomerProduct $customerProduct): self
    {
        foreach ($customerProduct->customerProductImages as $customerProductImage) {
            $this->deleteImage($customerProductImage);
        }
        return $this;
    }

    public function deleteImage(CustomerProductImage $customerProductImage)
    {
        if ($customerProductImage->file_id) {
            //(new FileRepository())->deleteFile($customerProductImage->file);
        }
        $customerProductImage->delete();
    }

    public function setImages(CustomerProduct $customerProduct, array $urls): self
    {
        return $this->clearImages($customerProduct)->saveImages($customerProduct, $urls);
    }

    public function update(CustomerProduct $customerProduct, Product $product): self
    {
        /*
        (new ProductImageRepository())->updateUrls(
            $product,
            $this->getUrls($customerProduct)
        );
        */
        return $this;
    }

    public function merge(CustomerProduct $customerProduct, Product $product): self
    {
        return $this->update($customerProduct, $product);
    }

    public function downloadImage(CustomerProductImage $customerProductImage): void
    {
        $url = $customerProductImage->url;
        /*
        if (!empty($url) && $customerProductImage->file_id === null) {
            $file = (new FileRepository())->storeUrl($url);
            if ($file) {
                $customerProductImage->file_id = $file->id;
                $customerProductImage->save();
            }
        }
        */
    }

    public function getUrl(?CustomerProductImage $customerProductImage): ?string
    {
        if ($customerProductImage === null) {
            return null;
        }
        /*
        $file = $customerProductImage->file;
        if ($file) {
            return (new FileRepository())->getUrl($file);
        } else {
            return $customerProductImage->url;
        }
        */
    }

    public function getCountByCustomerProduct($customerProduct): int
    {
        return $this->customerProductImage->where('customer_product_id', $customerProduct->id)->count();
    }
}
