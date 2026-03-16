<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories()
    {
        return $this->categoryRepository->all();
    }

    public function getActiveCategories()
    {
        return $this->categoryRepository->getActive();
    }

    public function getCategoryById($id)
    {
        return $this->categoryRepository->findOrFail($id);
    }

    public function createCategory(array $data)
    {
        if (isset($data['image'])) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return $this->categoryRepository->create($data);
    }

    public function updateCategory($id, array $data)
    {
        $category = $this->categoryRepository->findOrFail($id);

        if (isset($data['image']) && $data['image']) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $this->uploadImage($data['image']);
        }

        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory($id)
    {
        $category = $this->categoryRepository->findOrFail($id);

        // Delete image
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        return $this->categoryRepository->delete($id);
    }

    protected function uploadImage($image)
    {
        return $image->store('categories', 'public');
    }
}
