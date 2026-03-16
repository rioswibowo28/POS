<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\InventoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private InventoryRepository $inventoryRepository
    ) {}

    public function index(Request $request)
    {
        $query = $this->productRepository->with(['category', 'inventory']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query = $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query = $query->where('category_id', $request->category_id);
        }

        // Status filter
        if ($request->filled('is_active')) {
            $query = $query->where('is_active', $request->is_active);
        }

        // Favorite filter
        if ($request->filled('is_favorite')) {
            $query = $query->where('is_favorite', $request->is_favorite);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        $categories = $this->categoryRepository->all();

        return view('products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'is_favorite' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_available'] = $request->has('is_available');
        $validated['is_favorite'] = $request->has('is_favorite');

        $this->productRepository->create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function edit($id)
    {
        $product = $this->productRepository->find($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $id,
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'is_favorite' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_available'] = $request->has('is_available');
        $validated['is_favorite'] = $request->has('is_favorite');

        $this->productRepository->update($id, $validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $this->productRepository->delete($id);
        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }

    public function getStock($id)
    {
        $product = $this->productRepository->with(['inventory'])->find($id);
        return response()->json([
            'product' => $product,
            'inventory' => $product->inventory
        ]);
    }

    public function updateStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'min_quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $inventory = $this->inventoryRepository->getByProduct($id);
        
        if ($inventory) {
            $this->inventoryRepository->update($inventory->id, $validated);
        } else {
            $validated['product_id'] = $id;
            $this->inventoryRepository->create($validated);
        }

        return redirect()->route('products.index')->with('success', 'Stock updated successfully');
    }

    /**
     * Bulk insert multiple products
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.category_id' => 'required|exists:categories,id',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.cost' => 'nullable|numeric|min:0',
            'products.*.sku' => 'nullable|string|max:255',
            'products.*.description' => 'nullable|string',
            'products.*.is_active' => 'nullable',
            'products.*.is_available' => 'nullable',
        ]);

        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($request->products as $productData) {
                $data = [
                    'name' => $productData['name'],
                    'category_id' => $productData['category_id'],
                    'price' => $productData['price'],
                    'cost' => $productData['cost'] ?? null,
                    'sku' => $productData['sku'] ?? null,
                    'description' => $productData['description'] ?? null,
                    'is_active' => isset($productData['is_active']) ? true : false,
                    'is_available' => isset($productData['is_available']) ? true : false,
                    'is_favorite' => false,
                ];
                $this->productRepository->create($data);
                $count++;
            }
            DB::commit();
            return redirect()->route('products.index')->with('success', "Berhasil menambahkan {$count} produk baru.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('products.index')->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update selected products
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'bulk_action' => 'required|string',
        ]);

        $ids = $request->product_ids;
        $action = $request->bulk_action;
        $count = count($ids);

        DB::beginTransaction();
        try {
            switch ($action) {
                case 'activate':
                    \App\Models\Product::whereIn('id', $ids)->update(['is_active' => true]);
                    $message = "{$count} produk berhasil diaktifkan.";
                    break;

                case 'deactivate':
                    \App\Models\Product::whereIn('id', $ids)->update(['is_active' => false]);
                    $message = "{$count} produk berhasil dinonaktifkan.";
                    break;

                case 'set_available':
                    \App\Models\Product::whereIn('id', $ids)->update(['is_available' => true]);
                    $message = "{$count} produk berhasil diset tersedia.";
                    break;

                case 'set_unavailable':
                    \App\Models\Product::whereIn('id', $ids)->update(['is_available' => false]);
                    $message = "{$count} produk berhasil diset tidak tersedia.";
                    break;

                case 'set_favorite':
                    \App\Models\Product::whereIn('id', $ids)->update(['is_favorite' => true]);
                    $message = "{$count} produk berhasil diset sebagai favorit.";
                    break;

                case 'unset_favorite':
                    \App\Models\Product::whereIn('id', $ids)->update(['is_favorite' => false]);
                    $message = "{$count} produk berhasil dihapus dari favorit.";
                    break;

                case 'change_category':
                    $request->validate(['bulk_category_id' => 'required|exists:categories,id']);
                    \App\Models\Product::whereIn('id', $ids)->update(['category_id' => $request->bulk_category_id]);
                    $message = "{$count} produk berhasil dipindahkan ke kategori baru.";
                    break;

                case 'adjust_price':
                    $request->validate([
                        'price_adjustment_type' => 'required|in:fixed,percentage',
                        'price_adjustment_value' => 'required|numeric',
                    ]);
                    $type = $request->price_adjustment_type;
                    $value = $request->price_adjustment_value;

                    $products = \App\Models\Product::whereIn('id', $ids)->get();
                    foreach ($products as $product) {
                        if ($type === 'fixed') {
                            $newPrice = max(0, $product->price + $value);
                        } else {
                            $newPrice = max(0, $product->price * (1 + $value / 100));
                        }
                        $product->update(['price' => round($newPrice)]);
                    }
                    $message = "{$count} produk berhasil diupdate harganya.";
                    break;

                default:
                    throw new \Exception('Aksi tidak dikenali.');
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('products.index')->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected products
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        $ids = $request->product_ids;
        $count = count($ids);

        DB::beginTransaction();
        try {
            // Delete associated images
            $products = \App\Models\Product::whereIn('id', $ids)->get();
            foreach ($products as $product) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
            }

            \App\Models\Product::whereIn('id', $ids)->delete();

            DB::commit();
            return redirect()->route('products.index')->with('success', "Berhasil menghapus {$count} produk.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('products.index')->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
