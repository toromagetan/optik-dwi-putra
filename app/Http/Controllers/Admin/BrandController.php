<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.form', ['brand' => new Brand]);
    }

    public function store(Request $request)
    {
        $brand = Brand::create($this->validated($request));
        AuditLog::record('brand.created', $brand, ['name' => $brand->name]);

        return redirect()->route('admin.brands.index')->with('success', 'Merek berhasil ditambahkan.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.form', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $brand->update($this->validated($request, $brand));
        AuditLog::record('brand.updated', $brand, ['name' => $brand->name]);

        return redirect()->route('admin.brands.index')->with('success', 'Merek berhasil diperbarui.');
    }

    public function destroy(Brand $brand)
    {
        AuditLog::record('brand.deleted', $brand, ['name' => $brand->name]);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Merek berhasil dihapus. Produk yang memakainya kini tanpa merek.');
    }

    private function validated(Request $request, ?Brand $brand = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand?->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
