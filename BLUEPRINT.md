# BLUEPRINT E-COMMERCE: OPTIK DWI PUTRA (PROFESSIONAL TIER)
Berdasarkan benchmark dari Zonakacamata.com.

## 1. STRATEGI MARKETING & COPYWRITING
- **Penamaan Produk:** Menggunakan Benefit-First Naming (contoh: "Ergonomic Design Suri", "Asian Fit Aviator").
- **Penamaan Varian Warna:** Menggunakan kosakata fashion/premium (Jet Black, Torto, Honey, Rose, Ice, Clear).
- **Pilar Kepercayaan (USPs):**
  1. *Free Eye Examination* (Periksa mata gratis tanpa tekanan beli).
  2. *Premium & Reliable Materials* (Acetate, Titanium, dll).
  3. *Garansi Layanan* (Jika ada).

## 2. STACK TEKNOLOGI
- **Framework:** Laravel 11
- **Admin Panel:** Filament PHP v3 (Bawaan Laravel ekosistem).
- **Frontend:** Laravel Blade + Livewire + Tailwind CSS + Alpine.js (Untuk interaksi dinamis seperti ganti warna kacamata tanpa reload).
- **Payment Gateway:** Midtrans (QRIS, VA, GoPay).

## 3. ARSITEKTUR DATABASE LENGKAP (REVISI PROFESIONAL)

### A. Master Data
1. `categories` (Frame, Sunglasses, Lensa).
2. `products` (Data Induk Frame/Kacamata).
   - *Kolom:* `name`, `slug`, `description`, `material`, `base_price`, `is_active`.

### B. Sistem Varian (SKU & Warna)
3. `product_variants` (Warna spesifik dari satu model kacamata).
   - *Kolom:* `product_id`, `color_name` (ex: "Torto"), `color_hex` (ex: "#5C4033"), `image_path`, `stock`, `sku`.

### C. Sistem E-Commerce (Cart & Checkout)
4. `carts` & `cart_items` (Keranjang belanja user, termasuk data resep mata yang diinput sementara).
5. `orders` (Data transaksi final).
   - *Kolom:* `order_number`, `customer_name`, `customer_phone`, `customer_address`, `total_amount`, `payment_status` (Unpaid, Paid, Cancelled), `shipping_receipt`.
6. `order_items` (Item yang dibeli).
7. `order_prescriptions` (Resep mata untuk kacamata spesifik di order tersebut).
   - *Kolom:* `order_item_id`, `right_sph`, `right_cyl`, `right_axis`, `left_sph`, `left_cyl`, `left_axis`, `pupillary_distance` (PD).

## 4. ALUR USER EXPERIENCE (UX)
1. **Catalog:** User melihat daftar produk (Gambar otomatis berganti saat warna/varian di-klik).
2. **Product Page:** User melihat detail, USPs, dan klik "Pilih Lensa".
3. **Lens Wizard (Pop-up/Slide):** User memilih tipe lensa (Normal / Minus) -> Input Resep (SPH, CYL, AXIS).
4. **Checkout:** User isi alamat -> Pilih Kurir -> Bayar via Midtrans otomatis.
5. **Admin:** Optik Dwi Putra mendapat notifikasi di Filament Admin -> Proses pembuatan kacamata -> Kirim resi.

## 5. TAHAPAN PENGERJAAN (ROADMAP)
- [x] **Phase 1 (Data Structure):** Membuat tabel `product_variants`, `orders`, `order_items`, `order_prescriptions`. *(Selesai — tabel `carts`/`cart_items` menunggu Phase 5.)*
- [x] **Phase 2 (Admin Panel):** Menyesuaikan form Filament agar Admin bisa upload banyak foto/warna untuk 1 produk. *(Selesai — repeater varian + foto per varian.)*
- [x] **Phase 3 (Frontend Engine):** Membuat Product Page dengan fitur Ganti Warna dinamis (Livewire/Alpine). *(Selesai 2026-10-12 — halaman `/produk/{slug}`, foto berganti per varian, USP, tombol "Pilih Lensa".)*
- [x] **Phase 4 (Lens Wizard):** Form dinamis untuk input resep mata sebelum masuk keranjang. *(Selesai dalam bentuk sederhana via WhatsApp — wizard lensa + resep di beranda & halaman produk, tombol "Hubungi" (WhatsApp), tanpa penyimpanan order/customer sesuai keputusan owner tahap ini.)*
- [ ] **Phase 5 (Checkout & Payment):** Integrasi Midtrans. *(Tertunda — membutuhkan akun & kunci Midtrans dari owner; alur pemesanan aktif saat ini via WhatsApp.)*
