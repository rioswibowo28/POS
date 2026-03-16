<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts()
    {
        return $this->productRepository->withRelations();
    }

    public function getAvailableProducts()
    {
        return $this->productRepository->getAvailable();
    }

    public function getProductsByCategory($categoryId)
    {
        return $this->productRepository->getByCategory($categoryId);
    }

    public function searchProducts($keyword)
    {
        return $this->productRepository->search($keyword);
    }

    public function getProductById($id)
    {
        return $this->productRepository->with(['category', 'variants', 'modifiers', 'inventory'])->findOrFail($id);
    }

    public function createProduct(array $data)
    {
        if (isset($data['image'])) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return $this->productRepository->create($data);
    }

    public function updateProduct($id, array $data)
    {
        $product = $this->productRepository->findOrFail($id);

        if (isset($data['image']) && $data['image']) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $this->uploadImage($data['image']);
        }

        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct($id)
    {
        $product = $this->productRepository->findOrFail($id);

        // Delete image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return $this->productRepository->delete($id);
    }

    protected function uploadImage($image)
    {
        return $image->store('products', 'public');
    }
}
