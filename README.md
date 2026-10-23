# Optik Dwi Putra

Website e-commerce **hybrid satu toko** untuk Optik Dwi Putra: katalog resmi milik sendiri dengan keranjang yang diteruskan ke marketplace, WhatsApp, atau Telegram. Website tidak memproses pembayaran pada MVP; stok dikelola manual dari satu dashboard admin.

**Status:** aplikasi **sudah dibangun** (24 Oktober 2026) — Fase 1 s.d. 6 Roadmap selesai di lingkungan pengembangan: fondasi Laravel 13, database 8 tabel, panel admin Blade kustom, storefront, keranjang + handoff kanal, dan suite pengujian kriteria penerimaan (43 pengujian hijau). Katalog terisi 10 produk dummy × 3 gambar contoh. Belum diluncurkan ke produksi — Fase 7 menunggu penetapan domain & hosting final.

Repository sebelumnya dikosongkan pada 24 Oktober 2026 (commit pengosongan menyimpan seluruh versi sebelumnya pada histori commit sebelumnya, termasuk aset foto banner dan varian produk — dapat diambil kembali bila dibutuhkan).

## Dokumen

- **Blueprint** — *Spesifikasi Teknis Final — E-commerce Hybrid Satu Toko* v1.0 (24 Oktober 2026), dokumen acuan implementasi dari pemilik bisnis.
- [`docs/PRD.md`](docs/PRD.md) — kebutuhan produk: ruang lingkup MVP, kebutuhan fungsional (SF/AD), aturan keranjang dan kanal, model data, kriteria penerimaan, dan keputusan terbuka.
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — urutan pembangunan Fase 0 (discovery) sampai Fase 7 (peluncuran) beserta gerbang kelulusan tiap fase.

## Menjalankan di lingkungan pengembangan

Prasyarat: PHP 8.3, Composer, MySQL 8, Node 20+.

```bash
composer install
npm install && npm run build
cp .env.example .env   # sesuaikan DB_* dengan database lokal
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve --port=8010
```

- Toko: `http://localhost:8010/` · Katalog: `/katalog` · Keranjang: `/keranjang`
- Panel admin: `http://localhost:8010/masuk` — akun awal hasil seeder: **admin@optikdwiputra.id** / **password** (khusus pengembangan; WAJIB diganti sebelum produksi).
- Pengujian: `php artisan test` (memakai database `dwiputra_test`).

Semua pengaturan toko (nama, logo, kontak, nomor WhatsApp, tautan Telegram/sosial/marketplace, teks promosi, batas keranjang) diatur dari **Panel Admin → Pengaturan Toko** — lihat keputusan PRD §11.1.

## Langkah berikutnya

Fase 7 — Peluncuran: tetapkan domain & hosting final (PRD §11 K-1/K-2), ganti seluruh data dummy (katalog, nomor WhatsApp, tautan kanal, kata sandi admin), aktifkan HTTPS + backup, lalu UAT pemilik di domain produksi.
