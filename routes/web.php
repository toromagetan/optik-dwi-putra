<?php
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPrescription;
use App\Models\ProductVariant;
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

Route::post('/checkout', function (Request $request) {
    // 1. Create Order
    $order = Order::create([
        'order_number' => 'ODP-' . time(),
        'customer_name' => $request->customer_name ?? 'Guest',
        'customer_whatsapp' => $request->customer_whatsapp ?? '-',
        'shipping_address' => $request->shipping_address ?? '-',
        'total_amount' => $request->total_price,
        'status' => 'pending'
    ]);

    // 2. Create Order Item
    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_variant_id' => $request->variant_id,
        'price' => $request->total_price,
        'quantity' => 1,
        'subtotal' => $request->total_price,
    ]);

    // 3. Create Prescription if provided
    OrderPrescription::create([
        'order_item_id' => $item->id,
        'lens_type' => $request->lens_type,
        'right_sph' => $request->r_sph,
        'right_cyl' => $request->r_cyl,
        'right_axis' => $request->r_axis,
        'left_sph' => $request->l_sph,
        'left_cyl' => $request->l_cyl,
        'left_axis' => $request->l_axis,
        'pd' => $request->pd,
    ]);

    // 4. Generate WA Link & Redirect
    $settings = SiteSetting::first();
    $variant = ProductVariant::with('product')->find($request->variant_id);
    
    $text = "Halo {$settings->shop_name}, saya telah melakukan pemesanan via website.

" .
            "*Order ID:* {$order->order_number}
" .
            "*Nama:* {$order->customer_name}
" .
            "*Produk:* {$variant->product->name} (Warna: {$variant->color_name})
" .
            "*Lensa:* {$request->lens_type}
" .
            "*Total Tagihan:* Rp " . number_format($order->total_amount, 0, ',', '.') . "

" .
            "Mohon info metode pembayaran (BCA/QRIS).";
            
    return redirect("https://wa.me/{$settings->whatsapp_number}?text=" . urlencode($text));
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
