# PRD — Optik Dwi Putra: E-Commerce Hybrid Satu Toko

Versi 1.0 · 24 Oktober 2026
Sumber keputusan: *Blueprint "Spesifikasi Teknis Final — E-commerce Hybrid Satu Toko" v1.0 (24 Oktober 2026)* dari pemilik bisnis. Dokumen ini adalah turunan produk dari blueprint tersebut, diterapkan pada Optik Dwi Putra. Bila PRD ini bertentangan dengan blueprint, blueprint yang menang sampai pemilik bisnis memutuskan lain.

---

## 1. Ringkasan produk

Optik Dwi Putra adalah website e-commerce **hybrid** untuk **satu toko optik**: website berfungsi sebagai katalog resmi dan etalase merek, sedangkan transaksi diselesaikan di kanal yang sudah dipakai pelanggan — **marketplace, WhatsApp, atau Telegram**. Pelanggan memilih produk di website, memasukkannya ke keranjang, lalu meneruskan keranjangnya ke salah satu kanal tersebut. Pada MVP, website **tidak** memproses pembayaran, **tidak** menyediakan akun pelanggan, dan **tidak** menyinkronkan stok secara otomatis.

Satu dashboard admin dipakai pemilik/admin toko untuk mengelola katalog (produk, kategori, merek, gambar, harga, stok manual, tautan kanal) dan pengaturan toko.

## 2. Latar belakang

- Penjualan optik berjalan melalui marketplace dan percakapan chat; belum ada etalase resmi milik sendiri yang menampilkan katalog utuh dengan harga dan ketersediaan yang terkendali.
- Versi website sebelumnya (arsip pada histori repository) digantikan penuh: proyek ini adalah **pembangunan ulang dari nol** berdasarkan blueprint baru, bukan kelanjutan kode lama.
- Model hybrid dipilih agar toko memperoleh katalog profesional dan jalur pembelian yang jelas **tanpa** membangun sistem pembayaran dan tanpa bergantung pada integrasi marketplace yang tidak tersedia secara resmi.

## 3. Tujuan dan non-tujuan

### 3.1 Tujuan MVP
1. Katalog online resmi Optik Dwi Putra yang cepat, mobile-first, dan ramah mesin pencari.
2. Pengelolaan katalog mandiri oleh admin: produk, kategori, merek, gambar, harga, stok manual, tautan kanal, pengaturan toko.
3. Keranjang belanja anonim (tersimpan di browser) yang dapat diteruskan ke marketplace, WhatsApp, atau Telegram dengan ringkasan yang tervalidasi dari data backend terbaru.
4. Keamanan dasar yang terbukti: autentikasi admin, otorisasi server-side, validasi input dan unggahan, audit aktivitas penting, backup yang dapat dipulihkan.

### 3.2 Bukan tujuan (di luar MVP)
- Checkout dan pembayaran di dalam website (termasuk payment gateway).
- Akun, registrasi, dan riwayat pesanan pelanggan.
- Multi-toko, multi-tenant, atau dashboard penjual terpisah.
- Sinkronisasi stok/harga otomatis dengan marketplace.
- Klaim status pesanan ("pesanan diterima", "stok dipesan", "pembayaran berhasil") dari tindakan membuka kanal — tindakan itu hanya membuka percakapan/halaman kanal.

## 4. Pengguna dan peran

| Peran | Deskripsi | Hak utama |
|---|---|---|
| Pengunjung / pelanggan | Anonim, tanpa akun | Melihat katalog & detail produk, mencari, memfilter kategori, memakai keranjang, memilih kanal transaksi |
| Admin toko | Satu peran awal (dapat diperluas kemudian) | Seluruh pengelolaan katalog, stok, harga, gambar, kanal, pengaturan toko; aktivitas penting tercatat di audit log |

Tidak ada registrasi publik. Akun admin dibuat oleh pemilik sistem.

## 5. Keputusan yang dikunci (dari blueprint)

1. **Satu toko, satu katalog, satu dashboard admin.**
2. **Stok dikelola manual** oleh admin (kuantitas dan/atau status ketersediaan). Tidak ada sinkronisasi eksternal.
3. **Keranjang = pilih kanal transaksi**: marketplace / WhatsApp / Telegram. Website tidak memproses pembayaran pada MVP.
4. **Arsitektur modular monolith**: Laravel (Blade + JavaScript), MySQL. Tanpa microservices, tanpa REST API publik terpisah pada MVP.
5. **Harga dan status publikasi otoritatif berasal dari backend.** Isi keranjang di browser (ID produk + kuantitas) bukan sumber kebenaran harga.
6. **Semua fungsi administratif dilindungi autentikasi + otorisasi server-side.**
7. **Produk diarsipkan, bukan dihapus permanen**, bila sudah memiliki riwayat perubahan; penghapusan kategori yang masih dipakai ditolak atau wajib memindahkan produknya dulu.
8. Versi Laravel/PHP/dependensi **ditetapkan pada Fase 0 (discovery)** berdasarkan dukungan keamanan dan kompatibilitas hosting terkini — bukan dikunci di dokumen ini.

## 6. Kebutuhan fungsional

### 6.1 Storefront pelanggan

| ID | Kebutuhan | Ketentuan |
|---|---|---|
| SF-01 | Beranda | Identitas toko, kategori utama, produk unggulan, promosi aktif |
| SF-02 | Katalog | Hanya produk berstatus publikasi `published` yang tampil |
| SF-03 | Pencarian | Berdasarkan nama, SKU, dan kata kunci yang ditentukan |
| SF-04 | Kategori | Filter produk per kategori; kategori nonaktif tidak tampil |
| SF-05 | Detail produk | Gambar, nama, harga, deskripsi, SKU, ketersediaan |
| SF-06 | Galeri gambar | Gambar utama + gambar tambahan bila ada, urutan konsisten |
| SF-07 | Kanal produk | Tautan marketplace aktif untuk produk ditampilkan di detail |
| SF-08 | Keranjang | Tambah item, ubah kuantitas, hapus item, lihat subtotal |
| SF-09 | Pilih kanal | Teruskan keranjang via marketplace, WhatsApp, atau Telegram |
| SF-10 | Status produk | Produk habis/tidak aktif/tidak tersedia ditangani dengan jelas |
| SF-11 | Mobile-first | Navigasi, kartu produk, keranjang, dan tombol aksi nyaman di ponsel |
| SF-12 | SEO dasar | Slug produk stabil, metadata, canonical, sitemap |

### 6.2 Dashboard admin

| ID | Kebutuhan | Ketentuan |
|---|---|---|
| AD-01 | Autentikasi | Login, logout, sesi aman, pembatasan percobaan login |
| AD-02 | Ringkasan | Jumlah produk aktif, produk habis, status katalog |
| AD-03 | Produk | Buat, lihat, ubah, arsipkan, publikasikan produk |
| AD-04 | Harga | Harga jual rupiah dengan tipe data yang tepat (lihat §7) |
| AD-05 | Stok | Perbarui kuantitas atau status ketersediaan secara manual |
| AD-06 | Kategori | Kelola kategori dan status aktifnya |
| AD-07 | Gambar | Unggah, urutkan, ganti, hapus gambar produk (dengan validasi berkas) |
| AD-08 | Tautan produk | Kelola tautan marketplace per produk |
| AD-09 | Pengaturan toko | Nama, logo, kontak, tautan media sosial, kontak WhatsApp/Telegram |
| AD-10 | Validasi | Input tidak valid ditolak dengan pesan kesalahan yang jelas |
| AD-11 | Audit aktivitas | Perubahan penting (terutama harga & stok) tercatat dengan pelaku dan waktunya |
| AD-12 | Proteksi | Semua operasi mutasi wajib autentikasi + otorisasi |

### 6.3 Keranjang dan perpindahan kanal (aturan perilaku)

1. Pelanggan menambah produk dari katalog/detail. Browser menyimpan **ID produk + kuantitas** saja.
2. Saat keranjang dibuka atau akan diteruskan, backend mengambil data terbaru dan **memvalidasi harga serta ketersediaan** setiap item; subtotal dihitung dari harga tervalidasi.
3. Perubahan harga/stok/produk nonaktif yang terjadi setelah item masuk keranjang **ditangani sebelum ringkasan diteruskan** (item bermasalah ditandai/dikeluarkan dengan pemberitahuan).
4. Keranjang kosong tidak dapat diteruskan.
5. **Marketplace**: gunakan tautan khusus produk bila tersedia. Untuk keranjang multi-produk, MVP menampilkan pilihan yang jelas untuk membuka halaman marketplace produk satu per satu atau membatalkan — tidak ada klaim perpindahan keranjang otomatis.
6. **WhatsApp**: sistem menyusun **satu pesan** berisi daftar produk, SKU, kuantitas, harga referensi, subtotal, dan catatan bahwa ketersediaan serta harga akhir dikonfirmasi admin. Pelanggan menekan kirim sendiri.
7. **Telegram**: tampilkan tautan/kontak toko; bila pengiriman ringkasan otomatis tidak tersedia, sediakan ringkasan yang dapat disalin beserta tautan menghubungi toko.
8. Kegagalan membuka kanal **tidak menghapus** isi keranjang.

## 7. Model data (ringkasan; rancangan rinci di blueprint §4)

Delapan tabel inti: `users`, `categories`, `brands`, `products`, `product_images`, `product_channels`, `store_settings`, `audit_logs`.

Aturan data yang mengikat:
- Harga: **BIGINT (rupiah utuh)**; stok: bilangan bulat tak negatif; validasi batas di server.
- `products.sku` dan `products.slug` unik; slug stabil untuk SEO.
- Status produk dipisah dua sumbu: **publikasi** (`draft`, `published`, `archived`) dan **ketersediaan** (`available`, `out_of_stock`, `contact_for_availability`).
- Konsistensi stok: stok nol tidak boleh tampil sebagai tersedia tanpa kebijakan eksplisit; `contact_for_availability` berarti pelanggan wajib konfirmasi ke toko.
- `brands` opsional — produk boleh tanpa merek.
- `product_channels.channel_type` hanya nilai yang diizinkan; URL divalidasi per tipe kanal dan **menolak skema berbahaya** (mis. `javascript:`).
- `store_settings`: kunci unik, nilai tervalidasi per jenis pengaturan.
- `audit_logs`: pelaku, aksi, entitas, waktu, dan data perubahan yang aman.
- Indeks pada kolom pencarian katalog (`sku`, `slug`, `category_id`, status publikasi); foreign key ditegakkan.
- Urutan migrasi: users → categories & brands → products → product_images & product_channels → store_settings → audit_logs → indeks/constraint tambahan.

## 8. Kebutuhan non-fungsional

- **Keamanan**: otorisasi server-side pada semua mutasi admin; CSRF; validasi server-side menyeluruh; pembatasan percobaan login; validasi unggahan gambar (ukuran, ekstensi, MIME); tidak ada kredensial/rahasia di repository.
- **Kinerja & tampilan**: mobile-first; halaman katalog dan detail ringan; gambar produk dengan nama berkas aman dan ukuran wajar.
- **SEO**: URL produk berslug stabil, metadata dasar, canonical, sitemap.
- **Operasional**: HTTPS di produksi; backup database + gambar yang **terbukti dapat dipulihkan** pada lingkungan uji; logging kejadian penting dan aktivitas admin.
- **Keterujian**: setiap kriteria penerimaan (§9) dibuktikan dengan pengujian/pemeriksaan yang dapat diulang.

## 9. Kriteria penerimaan MVP

**Katalog & storefront** — (1) produk `draft`/`archived` tidak muncul di katalog publik; (2) pencarian, filter kategori, dan detail menampilkan data yang benar; (3) harga, gambar, SKU, ketersediaan sesuai database; (4) stok nol tidak tampil sebagai tersedia; (5) slug stabil + metadata SEO dasar terpasang; (6) tampilan layak di ponsel dan desktop.

**Dashboard admin** — (1) tanpa login, halaman pengelolaan tidak dapat dibuka; (2) input tidak valid ditolak dengan pesan jelas; (3) perubahan harga, stok, gambar, tautan tersimpan benar; (4) gambar tidak valid tidak dapat diunggah; (5) URL eksternal divalidasi dan menolak skema berbahaya; (6) perubahan harga dan stok tercatat di audit log.

**Keranjang & kanal** — (1) tambah/ubah kuantitas/hapus item berfungsi; (2) subtotal dihitung ulang dari harga backend tervalidasi; (3) keranjang kosong tidak dapat diteruskan; (4) perubahan harga/stok ditangani sebelum ringkasan diteruskan; (5) pesan WhatsApp memuat item, SKU, kuantitas, harga referensi, subtotal; (6) Telegram menyediakan jalur kontak + ringkasan yang dapat disalin; (7) tautan marketplace menuju URL produk yang benar; (8) kegagalan membuka kanal tidak menghapus keranjang.

**Keamanan & operasional** — (1) tidak ada mutasi admin tanpa otorisasi; (2) CSRF, validasi server-side, dan pembatasan login teruji; (3) HTTPS aktif di produksi; (4) backup database + gambar dapat dipulihkan di lingkungan uji; (5) tidak ada temuan keamanan kritis/tinggi yang belum ditangani; (6) seluruh pengujian wajib lulus tanpa regresi fitur yang sudah diterima.

**Skenario uji wajib** (minimal): admin mengubah harga sesudah item masuk keranjang; admin menonaktifkan produk yang ada di keranjang; kuantitas nol/negatif/di atas batas; URL marketplace salah atau mati; penyegaran browser dan persistensi keranjang sesuai kebijakan; gambar terlalu besar/ekstensi palsu/MIME tidak sesuai; akses endpoint admin tanpa autentikasi; pemulihan backup lalu perbandingan data katalog dengan sumbernya.

## 10. Asumsi dan ketergantungan

- Pemilik bisnis menyediakan: nama/domain final, identitas visual (logo, warna), daftar kategori awal, katalog produk awal (nama, SKU, harga, foto), tautan marketplace per produk, nomor WhatsApp dan tautan Telegram toko.
- Hosting mendukung PHP + MySQL sesuai versi runtime yang dipilih pada Fase 0.
- Aset foto dari website lama **tersedia di histori repository** (commit sebelum pengosongan) dan dapat dipakai kembali untuk katalog awal bila pemilik menghendaki.
- Marketplace tidak menyediakan integrasi keranjang resmi untuk MVP — perilaku §6.3 butir 5 adalah batas yang disengaja.

## 11. Keputusan terbuka (menunggu pemilik bisnis)

| # | Keputusan | Catatan / bawaan sementara |
|---|---|---|
| K-1 | Domain final | Kandidat tercatat dari proyek lama: subdomain `optik.istori.my.id` (DNS pernah disiapkan). Perlu konfirmasi dipakai lagi atau domain baru. |
| K-2 | Hosting & versi runtime | Menentukan versi Laravel/PHP final (Fase 0). Kandidat kuat: Laravel 13 + PHP 8.3 (pola proyek pemilik saat ini), menunggu cek kompatibilitas hosting. |
| K-3 | Identitas visual | Logo, warna merek, tipografi untuk storefront dan admin. |
| K-4 | Kategori & katalog awal | Daftar kategori perdana + produk awal yang dimuat saat peluncuran. |
| K-5 | Kanal yang aktif di MVP | Marketplace mana saja (Shopee/Tokopedia/dll.), nomor WhatsApp resmi, tautan Telegram — bawaan blueprint: ketiganya didukung, yang tampil hanya yang diisi admin. |
| K-6 | Kebijakan persistensi keranjang | Keranjang tersimpan di browser melewati penyegaran halaman (bawaan: persisten di perangkat yang sama); masa berlakunya ditetapkan di Fase 5. |
| K-7 | Panel admin: Filament atau Blade kustom | Blueprint hanya menetapkan "dashboard admin" Laravel; versi lama memakai Filament. Keputusan teknis di Fase 0/1 berdasarkan kecepatan bangun vs kendali tampilan. |

## 12. Di luar MVP (kandidat pengembangan lanjut, belum dijadwalkan)

- Checkout penuh di website + payment gateway (mis. Midtrans) dan manajemen pesanan.
- Integrasi/sinkronisasi marketplace (stok, harga, pesanan) bila jalur resmi tersedia.
- Akun pelanggan, riwayat pesanan, dan notifikasi status.
- Varian produk dan resep lensa terstruktur (wizard resep) seperti eksplorasi versi lama — hanya masuk bila pemilik memutuskannya secara eksplisit.

Setiap penambahan ruang lingkup dicatat dan disetujui pemilik bisnis **sebelum** implementasi; tidak ada fitur pembayaran yang masuk diam-diam.

### 11.1 Keputusan pemilik bisnis — 24 Oktober 2026 (sesi pembangunan)

Pemilik memutuskan pada awal pembangunan:

1. **K-7 ditutup: panel admin memakai Blade kustom** (bukan Filament) — lebih lengkap, mudah dipakai, dan menarik secara tampilan.
2. **Semua hal yang ditanyakan (K-1 s.d. K-6) diatur melalui panel admin** — identitas toko, logo, kontak, nomor WhatsApp, tautan Telegram/media sosial/marketplace, teks promosi, dan batas kuantitas keranjang tersimpan sebagai *store settings* dan dapat diubah tanpa menyentuh kode. Nilai awal memakai data dummy; penetapan nilai final (termasuk domain K-1 dan hosting K-2) menjadi pekerjaan sebelum peluncuran (Roadmap Fase 7), bukan penghalang pembangunan.
3. **K-4 untuk pembangunan: katalog awal = 10 produk dummy, masing-masing 3 gambar** (gambar contoh hasil generator program; diganti foto asli melalui panel admin).
4. **K-2 untuk pembangunan: Laravel 13 + PHP 8.3 + MySQL** (pola tumpukan pemilik saat ini), dikonfirmasi ulang terhadap hosting pada Fase 7.
5. **K-6 bawaan diterapkan:** keranjang persisten di browser yang sama (localStorage), melewati penyegaran halaman.

### 11.2 Penambahan atas instruksi pemilik — 24 Oktober 2026 (banner promo)

Pemilik meminta tampilan **dikombinasikan dengan versi sebelumnya yang memiliki banner besar terkait promo**. Maka ditambahkan **modul Banner Promo**:

- Tabel `banners` (di luar 8 tabel awal blueprint, atas instruksi pemilik): `title`, `subtitle`, `image_path`, `link_url`, `is_active`, `sort_order`.
- Beranda menampilkan **slider banner besar** tepat di bawah hero: gambar penuh + gradasi gelap + judul promo sebagai teks besar di atas gambar (pola versi sebelumnya), bergeser otomatis 6 detik, jeda saat kursor di atasnya, panah & titik navigasi, geser sentuh di ponsel. Banner tanpa gambar tampil sebagai panel gradasi emerald.
- Dikelola penuh dari panel admin (**Banner Promo**): tambah/ubah/hapus, unggah gambar tervalidasi isi berkas (maks 4 MB), tautan hanya http/https atau path internal, aktif/nonaktif, urutan tampil (tombol naik/turun). Semua aksi tercatat di audit log.
- Data contoh: 3 banner dengan naskah rekomendasi versi sebelumnya (Promo Akhir Tahun, Koleksi Premium, Periksa Mata Gratis) dan gambar ilustrasi generator berlabel "Foto contoh (dummy)".

### 11.3 Perubahan tema warna — 24 Oktober 2026 (instruksi pemilik)

Pemilik meminta warna lain yang lebih fresh dengan acuan **Optik Djava** dan **Zona Kacamata**. Hasil riset tampilan kedua situs (24 Okt 2026): Optik Djava memakai merah pekat (#a90000) di atas putih; Zona Kacamata memakai aksen biru **#175fab** dengan aksen amber **#f5a623** di atas putih/hitam bersih. Tema toko diubah menjadi:

- Warna utama `brand` = **biru segar** berjangkar #175fab (Zona Kacamata) — menggantikan emerald; seluruh tampilan storefront & panel admin mengikuti lewat token warna.
- Aksen `gold` = **amber #f5a623** — kehangatan visual (jembatan dari keberanian warna Djava) untuk penekanan harga/promo.
- Warna semantik TIDAK berubah: hijau WhatsApp (#25D366), hijau status aktif/terbit, dan amber peringatan tetap sebagaimana konvensi.
- Gambar contoh ikut diselaraskan: suasana banner menjadi biru/amber dan latar produk mint diganti biru es.

### 11.4 Warna oranye + tampilan luwes — 24 Oktober 2026 (instruksi pemilik)

Pemilik meminta dicoba **warna oranye** dan tampilan yang **tidak kaku**, dengan **Zona Kacamata sebagai rujukan tampilan (bukan warna)**:

- Token `brand` = skala **oranye** (jangkar #ea580c/#c2410c, tergelap #431407); aksen `gold` kembali ke amber cerah #f59e0b untuk kontras (lencana, label seksi, judul footer). Menggantikan tema biru §11.3 — pergantian cukup dari token pusat.
- Bahasa tampilan mengikuti ritme Zona Kacamata: kartu produk **tanpa bingkai/kotak** (gambar bersudut sangat bulat, teks lega di bawahnya), grid berjarak longgar, tombol berbentuk **pill**, judul seksi lebih besar dengan label kecil di atasnya, ubin kategori lembut tanpa garis, dan seksi Produk Terbaru sebagai **pita lembut oranye muda selebar layar**. Hero lebih lapang dengan judul lebih besar.
- Panel admin mengikuti warna lewat token; tata letaknya tetap utilitarian.
- Gambar contoh diselaraskan: suasana banner & pita label menjadi oranye/cokelat-oranye.

### 11.5 Header ramping + Ulasan Pelanggan — 24 Oktober 2026 (instruksi pemilik)

Pemilik menilai header terasa mengganggu dan belum ada tampilan ulasan berisi gambar tangkapan layar:

- **Header dirampingkan**: strip promo lebih tipis; header lebih rendah (padding & logo diperkecil); **baris cari kedua di mobile dicabut** — pencarian mobile kini berupa tombol ikon kaca pembesar di header yang membuka panel pencarian saat diketuk, sehingga header mobile tinggal satu baris (~60px) yang menempel saat scroll.
- **Modul Ulasan Pelanggan** (tabel `reviews` di luar 8 tabel blueprint, mengikuti pola Banner): nama, kota, sumber (Google/WhatsApp/Instagram/Tokopedia/Shopee/Lainnya), rating 1–5, isi ulasan, **gambar tangkapan layar opsional** (tervalidasi isi MIME, maks 4 MB), aktif/nonaktif, urutan tampil + geser urutan; CRUD penuh di panel admin "Ulasan Pelanggan" + audit log.
- **Seksi "Apa Kata Pelanggan"** di beranda (sesudah Produk Terbaru): ringkasan rating rata-rata + jumlah ulasan; ulasan bergambar tampil sebagai kartu tangkapan layar tinggi (geser menyamping di mobile); ulasan teks sebagai kartu testimoni berisi bintang, isi, dan identitas pelanggan.
- Data contoh: 5 ulasan dummy (2 bergambar tangkapan layar chat WA & kartu Google hasil generator, berlabel dummy) — diganti ulasan asli lewat panel admin.
