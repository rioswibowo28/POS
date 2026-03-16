<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::with(['items.product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('packages.index', compact('packages', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $packageData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            $packageData['image'] = $request->file('image')->store('packages', 'public');
        }

        $package = Package::create($packageData);

        foreach ($validated['items'] as $item) {
            PackageItem::create([
                'package_id' => $package->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('packages.index')->with('success', 'Paket berhasil dibuat');
    }

    public function edit($id)
    {
        $package = Package::with(['items.product'])->findOrFail($id);
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return response()->json([
            'package' => $package,
            'products' => $products,
        ]);
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $packageData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $packageData['image'] = $request->file('image')->store('packages', 'public');
        }

        $package->update($packageData);

        // Sync items: delete old, insert new
        $package->items()->delete();
        foreach ($validated['items'] as $item) {
            PackageItem::create([
                'package_id' => $package->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('packages.index')->with('success', 'Paket berhasil diupdate');
    }

    public function toggleActive($id)
    {
        $package = Package::findOrFail($id);
        $package->update(['is_active' => !$package->is_active]);

        $status = $package->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('packages.index')->with('success', "Paket berhasil {$status}");
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }
        
        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paket berhasil dihapus');
    }
}
