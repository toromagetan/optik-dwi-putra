<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index()
    {
        return view('admin.reviews.index', [
            'reviews' => Review::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.reviews.form', ['review' => new Review(['is_active' => true, 'rating' => 5])]);
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

        $review = Review::create($data);
        AuditLog::record('review.created', $review, ['name' => $review->name]);

        return redirect()->route('admin.reviews.index')->with('success', 'Ulasan berhasil ditambahkan.');
    }

    public function edit(Review $review)
    {
        return view('admin.reviews.form', ['review' => $review]);
    }

    public function update(Request $request, Review $review)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $path = $this->storeImage($request);
            if ($path === null) {
                return back()->withInput()->with('error', 'Berkas yang diunggah bukan gambar yang valid.');
            }
            $this->deleteImage($review);
            $data['image_path'] = $path;
        }

        $review->update($data);
        AuditLog::record('review.updated', $review, ['name' => $review->name]);

        return redirect()->route('admin.reviews.index')->with('success', 'Ulasan berhasil diperbarui.');
    }

    public function destroy(Review $review)
    {
        $this->deleteImage($review);
        AuditLog::record('review.deleted', $review, ['name' => $review->name]);
        $review->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }

    /** Geser urutan tampil ulasan. */
    public function move(Request $request, Review $review)
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $reviews = Review::orderBy('sort_order')->orderBy('id')->get()->values();
        $index = $reviews->search(fn ($r) => $r->id === $review->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && isset($reviews[$swapIndex])) {
            $other = $reviews[$swapIndex];
            [$review->sort_order, $other->sort_order] = [$other->sort_order, $review->sort_order];
            $review->save();
            $other->save();
        }

        return back()->with('success', 'Urutan ulasan diperbarui.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', Rule::in(Review::SOURCES)],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        return [
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'source' => $data['source'] ?? null,
            'rating' => (int) $data['rating'],
            'text' => $data['text'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /** Simpan gambar tangkapan layar ulasan; null bila MIME nyata bukan gambar. */
    private function storeImage(Request $request): ?string
    {
        $file = $request->file('image');

        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return null;
        }

        $name = Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension());

        return $file->storeAs('reviews', $name, 'public');
    }

    private function deleteImage(Review $review): void
    {
        if ($review->image_path) {
            Storage::disk('public')->delete($review->image_path);
        }
    }
}
