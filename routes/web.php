<?php
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Testimonial;
use App\Models\Brand;
use App\Models\Appointment;

Route::get('/', function () {
    $settings = SiteSetting::first();
    $categories = Category::orderBy('order_column')->get();
    $testimonials = Testimonial::where('is_active', true)->latest()->take(6)->get();
    $brands = Brand::where('is_active', true)->get();
    $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();
    // Load products with their variants
    $products = Product::with(['category', 'variants'])->where('is_active', true)->get();
    return view('welcome', compact('settings', 'categories', 'products', 'banners', 'testimonials', 'brands'));
});

Route::post('/book-appointment', function (Request $request) {
    $data = $request->validate([
        'customer_name' => 'required|string',
        'phone' => 'required|string',
        'type' => 'required|in:in_store,home_visit',
        'appointment_date' => 'required|date',
        'address' => 'nullable|string',
        'notes' => 'nullable|string'
    ]);
    
    Appointment::create($data);
    
    $settings = App\Models\SiteSetting::first();
    $wa = $settings->whatsapp_number ?? '628000000000';
    
    $typeStr = $data['type'] == 'home_visit' ? 'Home Visit (Datang ke Rumah)' : 'Datang ke Optik';
    $text = "Halo Optik Dwi Putra, saya ingin buat janji periksa mata:

"
          . "Nama: {$data['customer_name']}
"
          . "Layanan: {$typeStr}
"
          . "Tanggal: {$data['appointment_date']}
";
          
    if ($data['type'] == 'home_visit') {
        $text .= "Alamat: {$data['address']}
";
    }
    if (!empty($data['notes'])) {
        $text .= "Catatan: {$data['notes']}
";
    }
    
    return redirect('https://wa.me/' . $wa . '?text=' . urlencode($text));
})->name('book.appointment');
