<?php

namespace App\Http\Controllers\Api;

use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        try {
            $categories = $this->categoryService->getAllCategories();
            return $this->sendResponse($categories, 'Categories retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving categories', ['error' => $e->getMessage()], 500);
        }
    }

    public function active()
    {
        try {
            $categories = $this->categoryService->getActiveCategories();
            return $this->sendResponse($categories, 'Active categories retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving active categories', ['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $category = $this->categoryService->createCategory($request->all());
            return $this->sendResponse($category, 'Category created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating category', ['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);
            return $this->sendResponse($category, 'Category retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Category not found', ['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $category = $this->categoryService->updateCategory($id, $request->all());
            return $this->sendResponse($category, 'Category updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error updating category', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);
            return $this->sendResponse([], 'Category deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting category', ['error' => $e->getMessage()], 500);
        }
    }
}
