# BLUEPRINT PROYEK — OPTIK DWI PUTRA

**Status dokumen:** rencana / menunggu baseline audit dan persetujuan implementasi  
**Ruang lingkup:** storefront katalog optik mandiri dengan pencatatan inquiry dan tindak lanjut melalui WhatsApp  
**Sumber keputusan bisnis:** model inquiry tersimpan di database; harga lensa tampil dan dihitung ke total; backup pada hosting utama dan salinan cloud terpisah.

> Dokumen ini adalah blueprint target, bukan bukti bahwa fitur sudah diterapkan. Tidak ada perubahan aplikasi yang diotorisasi hanya dengan dokumen ini. Sebelum coding, lakukan baseline audit read-only, laporkan bukti dan risiko, lalu minta persetujuan pemilik proyek.

## 1. Tujuan dan batas scope

### Tujuan
1. Menyediakan katalog produk optik yang jelas, responsif, dan dapat dikelola melalui panel admin.
2. Mencatat inquiry ke database sebelum mengarahkan pelanggan ke WhatsApp.
3. Menampilkan harga frame dan pilihan lensa serta menghitung total secara konsisten di server.
4. Menjaga data pelanggan, resep optik, riwayat inquiry, serta akses admin.
5. Memiliki backup pada hosting utama dan salinan cloud terpisah yang benar-benar dapat dipulihkan.
6. Memastikan aplikasi dapat diuji, dirilis, dan dipelihara dengan prosedur yang terdokumentasi.

### Di luar scope saat ini
- Integrasi dengan Artesis POS, Toris, Istori, atau sistem lain.
- Multi-tenant atau sinkronisasi cabang.
- Payment gateway, pembayaran otomatis, keranjang belanja penuh, kurir otomatis, dan manajemen pengiriman seperti marketplace.
- Menganggap inquiry sebagai penjualan final. Konfirmasi penjualan dilakukan kemudian melalui proses bisnis yang ditentukan pemilik.

## 2. Kondisi awal dan aturan baseline

Repository yang diperiksa sebelumnya adalah `toromagetan/optik-dwi-putra`, branch `main`, pada commit `8956b4cd469b4eb6304934dcd150784e5da7f030`. Commit tersebut menghapus route database checkout dan mengarahkan pemesanan ke WhatsApp tanpa menyimpan inquiry. Blueprint lama di repository masih menyebut Laravel 11 dan Midtrans sehingga perlu diselaraskan dengan implementasi serta keputusan terbaru.

Informasi ini adalah titik awal dari pemeriksaan sebelumnya, bukan pengganti pemeriksaan baseline terbaru. Pada awal pekerjaan:
- catat HEAD, branch, status working tree, perubahan lokal, file untracked, dan commit remote;
- identifikasi versi aktual PHP/Laravel/dependensi dari manifest dan lockfile;
- petakan routes, controllers, models, migrations, Filament resources, seeder, backup service, upload/storage, dan tests;
- jangan menimpa atau menghapus perubahan lokal yang belum diaudit;
- jangan menjalankan migrasi destruktif atau restore pada database produksi.

## 3. Arsitektur target

### 3.1 Katalog publik
- Menampilkan kategori, brand, produk, harga frame, foto, varian, dan ketersediaan.
- Hanya menampilkan produk/varian yang diizinkan tampil dan dapat dipilih.
- Produk nonaktif dan varian nonaktif tidak dapat dipesan melalui manipulasi request.
- Stok ditampilkan sesuai kebijakan bisnis yang disepakati; server tetap menjadi sumber validasi akhir.
- Gambar menggunakan URL storage yang sesuai konfigurasi filesystem, dengan fallback jika aset tidak tersedia.
- Pertimbangkan pagination dan eager loading sesuai volume katalog aktual.

### 3.2 Pemilihan lensa dan kalkulasi
- Katalog memiliki daftar pilihan lensa yang aktif, deskripsi, dan harga yang dikelola admin.
- Harga frame dan harga lensa ditampilkan secara terpisah serta totalnya ditampilkan jelas.
- Definisi harga harus eksplisit: apakah harga lensa merupakan tambahan terhadap frame atau pilihan paket yang menggantikan harga dasar. Keputusan ini harus dikonfirmasi pada desain detail sebelum implementasi.
- Nilai total dari browser tidak dipercaya. Server mengambil harga yang aktif dari database, memvalidasi opsi, lalu menghitung total.
- Aturan pembulatan, diskon/promo, kuantitas, dan masa berlaku harga ditetapkan sebelum coding.

### 3.3 Inquiry tersimpan sebelum WhatsApp
Alur target:
1. Pelanggan memilih produk dan varian.
2. Pelanggan memilih opsi lensa serta mengisi data kontak yang diperlukan.
3. Jika resep optik dibutuhkan, tampilkan input dengan validasi format dan batas nilai yang disepakati; jangan meminta data yang tidak diperlukan.
4. Browser mengirim permintaan ke endpoint server dengan CSRF protection dan validasi.
5. Server memeriksa status produk/varian, stok, pilihan lensa, serta harga terkini.
6. Server menyimpan inquiry dan item/rincian harga dalam transaksi database.
7. Setelah penyimpanan berhasil, aplikasi menampilkan kode inquiry dan menawarkan untuk membuka WhatsApp dengan ringkasan yang sesuai.
8. Jika penyimpanan gagal, jangan membuka WhatsApp dengan pesan yang menyatakan inquiry telah tercatat.
9. Status inquiry diperbarui admin melalui alur yang terotorisasi.

Membuka WhatsApp tidak membuktikan pesan telah terkirim atau dibaca. Inquiry juga bukan bukti pembayaran atau penjualan final.

### 3.4 Data inquiry dan snapshot
Skema akhir harus ditentukan setelah audit migrations/models. Secara konseptual data perlu mencakup:
- inquiry: kode referensi unik, waktu, status, identitas kontak minimum yang diperlukan, total snapshot, dan metadata operasional yang relevan;
- item inquiry: referensi produk/varian, nama/SKU snapshot, opsi lensa snapshot, kuantitas, harga satuan, dan subtotal;
- resep optik: hanya bila diperlukan dan dengan akses lebih ketat daripada data katalog umum;
- timestamp dan audit perubahan status yang diperlukan untuk operasional.

Harga/nama/SKU yang disimpan sebagai snapshot mempertahankan konteks saat inquiry dibuat meskipun katalog berubah. Gunakan foreign key dan kebijakan penghapusan yang tidak merusak riwayat. Jangan menyimpan data kartu pembayaran atau data sensitif yang tidak diperlukan.

### 3.5 Status inquiry
Usulan awal (perlu persetujuan):
- `pending`: tercatat, menunggu ditindaklanjuti;
- `contacted`: admin telah menghubungi pelanggan;
- `confirmed`: detail pesanan telah disepakati;
- `cancelled`: dibatalkan;
- `completed`: proses bisnis selesai, jika status ini memang dibutuhkan.

Status harus memiliki transisi yang jelas, akses perubahan terbatas, dan catatan waktu/pelaku jika diperlukan. Jangan menyamakan `confirmed` dengan `paid` tanpa proses pembayaran yang nyata.

## 4. Model keamanan

- Terapkan autentikasi dan otorisasi eksplisit untuk panel Filament dan setiap resource sensitif.
- Pastikan akun demo/password bawaan tidak aktif pada produksi; tinjau jalur seeding dan pembuatan admin.
- Validasi server-side untuk semua field, ID, status, harga, jumlah, upload, URL, dan input resep.
- Batasi request inquiry dengan rate limiting dan mitigasi pengiriman berulang; gunakan idempotency atau mekanisme deduplikasi jika dibutuhkan.
- Gunakan mass assignment secara eksplisit dan tinjau pemakaian `$guarded = []`.
- Batasi akses data kontak dan resep optik berdasarkan peran; hindari menaruh resep pada log, analytics, URL, atau pesan yang tidak diperlukan.
- Tetapkan kebijakan retensi dan penghapusan data pelanggan serta prosedur menangani permintaan penghapusan.
- Pastikan produksi memakai HTTPS, `APP_DEBUG=false`, secret yang benar, permission minimal, dan document root ke direktori `public/`.
- Jangan mencatat password, token, kredensial cloud, atau data resep secara penuh ke log.

## 5. Panel administrasi dan operasional

Admin perlu mengelola:
- kategori, brand, produk, varian, SKU, stok, harga, harga promo bila digunakan, dan status publikasi;
- opsi lensa beserta harga dan status aktif;
- inquiry, item, status tindak lanjut, dan catatan operasional yang tidak berlebihan;
- appointment (jika fitur ini tetap menjadi scope), banner, testimonial, dan pengaturan toko;
- status backup terakhir dan hasil verifikasi, jika integrasi status backup aman untuk ditampilkan.

Setiap operasi CRUD harus memiliki validasi, otorisasi, kebijakan penghapusan yang menjaga histori, dan feedback yang jelas. Data inquiry/resep tidak boleh tersedia ke role yang tidak memerlukannya.

## 6. Strategi backup dan pemulihan

### Target
1. Backup lokal di hosting untuk pemulihan cepat.
2. Salinan terpisah di cloud/provider berbeda dari hosting utama untuk mitigasi kegagalan host/akun.

### Isi backup
- Database dengan prosedur dump yang sesuai database/hosting.
- File upload yang diperlukan (foto produk/varian, banner, dan aset yang dikelola admin).
- Metadata minimum untuk mengidentifikasi versi, waktu, dan hasil proses.

### Kontrol wajib
- Jangan hardcode path executable Windows pada aplikasi yang berjalan di Linux.
- Hindari shell command yang dibentuk dari input tanpa escaping/validasi kuat.
- Validasi nama/path file dan batasi restore hanya ke backup yang terdaftar/diizinkan.
- Periksa exit code, ukuran, checksum/integritas, dan status upload; keberadaan file saja bukan bukti backup valid.
- Enkripsi saat penyimpanan/transfer jika didukung, batasi kredensial cloud, dan jangan simpan secret di Git.
- Tetapkan jadwal, retensi, notifikasi kegagalan, dan biaya/kuota cloud.
- Sebelum restore, ambil snapshot kondisi target, verifikasi target database, dan gunakan guard terhadap restore bersamaan.
- Uji restore hanya di lingkungan disposable/staging; tidak melakukan eksperimen restore langsung pada produksi.
- Catat waktu backup terakhir yang sukses dan hasil restore drill.

### Sasaran operasional yang harus diputuskan
- RPO: berapa banyak perubahan data maksimum yang dapat hilang.
- RTO: berapa lama aplikasi boleh tidak tersedia selama pemulihan.
- Retensi harian/mingguan/bulanan, kapasitas file upload, dan siapa yang menerima notifikasi.
Nilai target tidak boleh diasumsikan tanpa persetujuan pemilik dan pertimbangan biaya/kapasitas.

## 7. Pengujian dan quality gates

### Inquiry/harga
- Inquiry valid tersimpan dan memiliki kode unik.
- Kegagalan database tidak menghasilkan respons sukses.
- Harga yang dimanipulasi di browser diabaikan; total dihitung dari harga server.
- Harga lensa masuk ke total sesuai aturan yang disepakati.
- Produk/varian/lensa nonaktif ditolak.
- Stok habis dan perubahan stok sebelum submit ditangani dengan benar.
- Klik ganda/retry tidak menghasilkan inquiry duplikat yang tidak diinginkan.
- Pesan WhatsApp merujuk pada inquiry yang benar; tidak mengklaim pesan telah terkirim.

### Authorization/privacy
- Pengguna anonim tidak dapat mengakses panel.
- Role tanpa izin tidak dapat membaca atau mengubah inquiry/resep.
- Validasi appointment dan rate limit diuji bila fitur tetap aktif.
- Upload divalidasi tipe/ukuran dan tidak memungkinkan eksekusi file berbahaya.

### Backup/recovery
- Backup DB dan aset selesai serta dapat diverifikasi.
- Kegagalan command/upload dilaporkan sebagai kegagalan.
- Restore test berhasil di database/lingkungan terpisah.
- File upload dan referensi database cocok setelah pemulihan.
- Restore bersamaan, path traversal, arsip rusak, dan kredensial salah ditangani aman.

### Rilis
- Jalankan lint/syntax check, test suite, regresi, dan pemeriksaan diff.
- Verifikasi konfigurasi staging dan smoke test dari alur publik sampai panel admin.
- Simpan bukti hasil tes dan catat test yang belum bisa dijalankan serta alasannya.

## 8. Deployment dan observabilitas

- Verifikasi dukungan hosting aktual terhadap versi PHP, ekstensi, Composer, build aset, scheduler, symlink/storage, permission, dan batasan `exec`.
- Gunakan `composer install` berbasis lockfile; jangan memperbarui dependensi secara tidak terencana.
- Jalankan migration hanya setelah backup dan rencana rollback ditinjau.
- Terapkan HTTPS, konfigurasi produksi, optimasi cache yang sesuai, dan log yang tidak membocorkan data.
- Sediakan health/smoke check, pemantauan error, dan catatan deployment/rollback.
- Jika hosting membatasi proses background atau command shell, gunakan mekanisme backup yang kompatibel dan telah diuji, bukan asumsi bahwa `exec` tersedia.

## 9. Aturan perubahan repository

- Audit read-only sebelum implementasi.
- Setiap tahap mempunyai scope file, risiko, rencana tes, dan persetujuan.
- Pertahankan file untracked/perubahan lokal; jangan menjalankan `git clean`, `git reset --hard`, atau restore massal.
- Hindari migrasi destruktif dan jangan menjalankan restore pada produksi untuk pengujian.
- Commit/push hanya setelah tes dan verifikasi independen disetujui.
- Blueprint ini tidak mengizinkan perubahan kode dengan sendirinya.

## 10. Definition of Done

Proyek dinyatakan siap rilis hanya jika:
- model inquiry dan harga disetujui dan lulus tes;
- akses admin dan data sensitif telah diuji;
- backup hosting dan salinan cloud telah diverifikasi;
- restore drill berhasil di lingkungan terpisah;
- deployment produksi mengikuti konfigurasi aman;
- dokumentasi sesuai perilaku aplikasi;
- laporan verifikasi akhir mencatat hasil tes, risiko tersisa, dan keputusan rilis.
