<?php

namespace App\Http\Controllers\Api;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        try {
            $products = $this->productService->getAllProducts();
            return $this->sendResponse($products, 'Products retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving products', ['error' => $e->getMessage()], 500);
        }
    }

    public function available()
    {
        try {
            $products = $this->productService->getAvailableProducts();
            return $this->sendResponse($products, 'Available products retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving available products', ['error' => $e->getMessage()], 500);
        }
    }

    public function byCategory($categoryId)
    {
        try {
            $products = $this->productService->getProductsByCategory($categoryId);
            return $this->sendResponse($products, 'Products by category retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving products', ['error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $products = $this->productService->searchProducts($request->keyword);
            return $this->sendResponse($products, 'Products search completed.');
        } catch (\Exception $e) {
            return $this->sendError('Error searching products', ['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $product = $this->productService->createProduct($request->all());
            return $this->sendResponse($product, 'Product created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating product', ['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = $this->productService->getProductById($id);
            return $this->sendResponse($product, 'Product retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Product not found', ['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $id,
            'barcode' => 'nullable|string|max:100',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $product = $this->productService->updateProduct($id, $request->all());
            return $this->sendResponse($product, 'Product updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error updating product', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->productService->deleteProduct($id);
            return $this->sendResponse([], 'Product deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting product', ['error' => $e->getMessage()], 500);
        }
    }
}
