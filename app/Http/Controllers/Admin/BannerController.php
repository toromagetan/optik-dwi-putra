<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        return view('admin.banners.index', [
            'banners' => Banner::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => new Banner(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request);
            if ($data['image_path'] === null) {
                return back()->withInput()->with('error', 'Berkas yang diunggah bukan gambar yang valid.');
            }
        }

        $banner = Banner::create($data);
        AuditLog::record('banner.created', $banner, ['title' => $banner->title]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $path = $this->storeImage($request);
            if ($path === null) {
                return back()->withInput()->with('error', 'Berkas yang diunggah bukan gambar yang valid.');
            }
            $this->deleteImage($banner);
            $data['image_path'] = $path;
        }

        $banner->update($data);
        AuditLog::record('banner.updated', $banner, ['title' => $banner->title]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        $this->deleteImage($banner);
        AuditLog::record('banner.deleted', $banner, ['title' => $banner->title]);
        $banner->delete();

        return back()->with('success', 'Banner berhasil dihapus.');
    }

    /** Geser urutan tampil banner (naik = tampil lebih dulu). */
    public function move(Request $request, Banner $banner)
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $banners = Banner::orderBy('sort_order')->orderBy('id')->get()->values();
        $index = $banners->search(fn ($b) => $b->id === $banner->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && isset($banners[$swapIndex])) {
            $other = $banners[$swapIndex];
            [$banner->sort_order, $other->sort_order] = [$other->sort_order, $banner->sort_order];
            $banner->save();
            $other->save();
        }

        return back()->with('success', 'Urutan banner diperbarui.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            // Tautan hanya boleh http/https atau path internal ("/...") —
            // skema berbahaya seperti javascript: ditolak.
            'link_url' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/i'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        return [
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /** Simpan gambar banner; null bila MIME nyata bukan gambar. */
    private function storeImage(Request $request): ?string
    {
        $file = $request->file('image');

        // Verifikasi MIME nyata berkas, bukan hanya ekstensi.
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return null;
        }

        $name = Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension());

        return $file->storeAs('banners', $name, 'public');
    }

    private function deleteImage(Banner $banner): void
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
    }
}
