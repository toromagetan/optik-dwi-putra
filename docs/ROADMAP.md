# ROADMAP — Optik Dwi Putra: E-Commerce Hybrid Satu Toko

Versi 1.0 · 24 Oktober 2026
Pasangan dokumen: `docs/PRD.md` (kebutuhan) dan blueprint *"Spesifikasi Teknis Final — E-commerce Hybrid Satu Toko" v1.0*. Roadmap ini mengatur **urutan** pembangunan; PRD mengatur **apa** yang dibangun. Perubahan urutan atau ruang lingkup memerlukan persetujuan pemilik bisnis dan dicatat di dokumen ini.

## Aturan main

1. **Per fase, bukan per berkas.** Satu fase diselesaikan, diuji, diverifikasi, lalu dilaporkan sebelum fase berikut dimulai.
2. **Setiap fase punya gerbang keluar.** Fase tidak dinyatakan selesai sebelum gerbangnya lulus dengan bukti (hasil uji, tangkapan layar, atau tautan yang dapat diperiksa ulang).
3. **Uji mengikuti fitur.** Pengujian relevan ditulis bersama implementasinya, bukan ditumpuk di akhir; Fase 6 adalah pengujian menyeluruh dan regresi, bukan pengujian pertama.
4. **Tidak ada ruang lingkup siluman.** Terutama: tidak ada fitur pembayaran/checkout yang masuk tanpa keputusan eksplisit pemilik (lihat PRD §12).
5. **Laporan per fase** kepada pemilik: hasil (lulus/blocker), lingkup perubahan, hasil uji, risiko tersisa.

## Fase 0 — Discovery dan keputusan

**Tujuan:** mengunci semua yang belum diputuskan blueprint agar pembangunan tidak menebak.

Pekerjaan:
- Konfirmasi keputusan terbuka PRD §11: domain final (K-1), hosting + versi runtime/Laravel/PHP (K-2), identitas visual (K-3), kategori & katalog awal (K-4), kanal aktif + nomor WhatsApp + tautan Telegram + tautan marketplace (K-5), kebijakan persistensi keranjang (K-6), pilihan panel admin (K-7).
- Inventarisasi aset lama dari histori repository (foto banner/varian) yang akan dipakai kembali.
- Tetapkan struktur repository, konvensi penamaan, dan strategi backup lingkungan pengembangan.

**Hasil:** daftar keputusan tertulis (lampiran PRD §11 terisi), spesifikasi dinyatakan disetujui.
**Gerbang keluar — Gerbang Desain:** pemilik menyetujui keputusan Fase 0 dan skema data PRD §7.

## Fase 1 — Fondasi aplikasi

**Tujuan:** aplikasi Laravel kosong yang berjalan aman dan dapat dikembangkan.

Pekerjaan:
- Inisialisasi proyek Laravel (versi hasil Fase 0), konfigurasi environment lokal, koneksi MySQL.
- Autentikasi admin: login, logout, sesi aman, pembatasan percobaan login; akun admin awal dari seeder yang aman (tanpa kredensial di repository).
- Kerangka logging + pola audit (tabel `audit_logs` dibuat di Fase 2; konvensi pencatatannya ditetapkan di sini).
- Tata letak dasar: storefront dan admin dapat dirender; aset dibangun (build pipeline) dan terverifikasi.

**Hasil:** aplikasi dasar berjalan; admin dapat login; halaman kosong storefront/admin tampil.
**Gerbang keluar:** login admin berfungsi dan teruji (akses tanpa login ditolak), build aset lulus, tidak ada rahasia di repository.

## Fase 2 — Database dan domain katalog

**Tujuan:** skema data final berdiri sesuai PRD §7.

Pekerjaan:
- Migrasi berurutan: `users` → `categories`, `brands` → `products` → `product_images`, `product_channels` → `store_settings` → `audit_logs` → indeks/constraint tambahan.
- Model + relasi + factory untuk kedelapan tabel; validasi domain dasar (harga, SKU/slug unik, status publikasi vs ketersediaan).
- Seeder katalog awal (kategori + contoh produk dari data Fase 0) yang idempoten.

**Hasil:** skema tervalidasi, migrasi bersih dari keadaan kosong dan dapat diulang (`migrate:fresh` lulus), factory tersedia untuk pengujian.
**Gerbang keluar:** migrasi + seeder lulus di database bersih; uji model/relasi pokok lulus.

## Fase 3 — Dashboard admin

**Tujuan:** admin dapat mengelola seluruh toko tanpa menyentuh database langsung.

Pekerjaan:
- Ringkasan admin (AD-02).
- CRUD produk + publikasi/arsip (AD-03), harga (AD-04), stok manual + status ketersediaan (AD-05).
- Kategori (AD-06) dan merek.
- Gambar produk: unggah tervalidasi (ukuran/ekstensi/MIME), urutan, ganti, hapus (AD-07).
- Tautan kanal per produk dengan validasi URL per tipe (AD-08).
- Pengaturan toko: nama, logo, kontak, media sosial, WhatsApp/Telegram (AD-09).
- Audit aktivitas untuk perubahan penting, terutama harga dan stok (AD-11); seluruh mutasi terproteksi (AD-12).

**Hasil:** katalog nyata dapat dimuat dan dipelihara penuh dari panel admin.
**Gerbang keluar — Gerbang Fungsional (parsial):** kriteria penerimaan kelompok B (PRD §9) lulus; uji otorisasi admin lulus (tanpa login = tertolak; input/URL/gambar tidak valid = tertolak).

## Fase 4 — Storefront

**Tujuan:** katalog publik tampil profesional dan siap ditinjau pemilik.

Pekerjaan:
- Beranda (SF-01), katalog + filter kategori (SF-02, SF-04), pencarian (SF-03).
- Detail produk + galeri (SF-05, SF-06) dan tautan kanal produk (SF-07).
- Penanganan status produk habis/nonaktif (SF-10); tampilan mobile-first (SF-11).
- SEO dasar: slug stabil, metadata, canonical, sitemap (SF-12).

**Hasil:** katalog siap ditinjau dengan data nyata dari Fase 3.
**Gerbang keluar:** kriteria penerimaan kelompok A (PRD §9) lulus; peninjauan visual pemilik selesai (tangkapan layar dibagikan, koreksi tampilan diterapkan).

## Fase 5 — Keranjang dan perpindahan kanal

**Tujuan:** alur pembelian hybrid berjalan utuh sesuai PRD §6.3.

Pekerjaan:
- Keranjang anonim di browser (ID produk + kuantitas), tambah/ubah/hapus, subtotal (SF-08).
- Validasi backend saat keranjang dibuka/diteruskan: harga dan ketersediaan terbaru; penanganan item yang berubah/habis/nonaktif (PRD §6.3 butir 2–4).
- Handoff WhatsApp: pesan ringkasan terformat (SF-09); handoff Telegram: tautan kontak + ringkasan dapat disalin; handoff marketplace: tautan per produk dengan pilihan yang jelas untuk keranjang multi-produk.
- Kegagalan membuka kanal tidak menghapus keranjang.

**Hasil:** pelanggan dapat menyusun keranjang dan meneruskannya ke kanal pilihannya.
**Gerbang keluar — Gerbang Fungsional (penuh):** kriteria penerimaan kelompok C (PRD §9) lulus, termasuk seluruh skenario uji wajib yang menyangkut keranjang.

## Fase 6 — Quality assurance menyeluruh

**Tujuan:** membuktikan MVP memenuhi seluruh kriteria penerimaan tanpa regresi.

Pekerjaan:
- Suite uji lengkap: unit, feature, keamanan (otorisasi, CSRF, throttle login, validasi unggahan & URL), responsif.
- Seluruh skenario uji wajib PRD §9 dijalankan dan hasilnya didokumentasikan.
- Uji pemulihan backup: backup database + gambar dipulihkan di lingkungan uji dan data katalog dibandingkan dengan sumbernya.

**Hasil:** laporan pengujian tertulis per kriteria penerimaan.
**Gerbang keluar — Gerbang Keamanan + Gerbang Penerimaan:** tidak ada temuan keamanan kritis/tinggi terbuka; seluruh kriteria PRD §9 berstatus lulus; pemilik menyetujui hasil UAT awal.

## Fase 7 — Peluncuran

**Tujuan:** website live di domain final dengan operasional yang aman.

Pekerjaan:
- Deploy ke hosting produksi, konfigurasi environment produksi, HTTPS aktif.
- Backup terjadwal aktif + satu kali drill pemulihan di lingkungan produksi-terpisah/uji.
- Monitoring dasar dan pencatatan error.
- UAT akhir pemilik di domain produksi; muat katalog perdana resmi.

**Hasil:** website digunakan pelanggan; prosedur backup/pemulihan terdokumentasi.
**Gerbang keluar — Gerbang Produksi:** HTTPS terverifikasi, backup + pemulihan terverifikasi, UAT pemilik lulus, tidak ada blocker terbuka.

## Pasca-MVP (belum dijadwalkan — menunggu keputusan pemilik)

- Checkout + payment gateway dan manajemen pesanan (PRD §12).
- Integrasi marketplace resmi bila tersedia.
- Akun pelanggan dan notifikasi.
- Varian produk / wizard resep lensa terstruktur.

## Definition of Done (berlaku setiap fase)

Fitur/pekerjaan dinyatakan selesai bila: sesuai spesifikasi yang disetujui; pengujian relevan ditulis dan lulus; tidak ada regresi; otorisasi + validasi server-side diperiksa; tidak ada rahasia masuk repository; dokumentasi diperbarui; hasil dapat diverifikasi dari bukti pengujian; status dan risiko tersisa dilaporkan dengan jelas.
