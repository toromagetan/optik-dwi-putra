# ROADMAP IMPLEMENTASI — OPTIK DWI PUTRA

**Status:** Proposed — menunggu baseline audit dan persetujuan pemilik proyek  
**Prinsip:** audit-first, perubahan kecil dan terukur, tes sebelum melanjutkan, verifikasi independen sebelum rilis.

## Ringkasan fase

| Fase | Fokus | Hasil / gerbang |
|---|---|---|
| 0 | Baseline audit read-only | Baseline terverifikasi dan daftar temuan berbukti; belum ada perubahan kode |
| 1 | Keamanan dan integritas | Risiko akses/data diklasifikasikan dan rencana perbaikan disetujui |
| 2 | Desain inquiry dan harga | Skema data, alur, status, dan aturan harga disetujui |
| 3 | Implementasi inquiry | Inquiry tersimpan sebelum WhatsApp; kalkulasi server-side; tes lulus |
| 4 | Backup dan recovery | Backup hosting + cloud, validasi, restore drill terpisah |
| 5 | Katalog, admin, UX | Varian, stok, gambar, dan operasional konsisten |
| 6 | QA, dokumentasi, staging | Regresi lulus, docs cocok, staging siap |
| 7 | Rilis dan pemantauan | Rilis hanya dengan persetujuan; smoke test dan rollback tersedia |

Urutan rinci dapat disesuaikan berdasarkan temuan baseline, terutama jika ada risiko P0 yang harus ditangani sebelum fitur baru.

## Fase 0 — Baseline audit read-only

### Aktivitas
- [ ] Catat repository, branch, HEAD SHA, remote, dan commit terbaru.
- [ ] Periksa working tree, perubahan lokal, file untracked, dan jangan menimpa residu.
- [ ] Verifikasi versi PHP/Laravel/Filament dan dependensi dari manifest serta lockfile.
- [ ] Petakan routes, controllers, models, migrations, Filament resources, seeders, services, storage, dan tests.
- [ ] Telusuri jalur inquiry/checkout WhatsApp yang saat ini tidak menyimpan inquiry.
- [ ] Audit backup/restore, jalur platform-spesifik, penggunaan shell, file upload, dan cloud belum terintegrasi.
- [ ] Periksa konfigurasi produksi yang tersedia tanpa menampilkan secret.
- [ ] Jalankan hanya pemeriksaan read-only yang aman; jangan melakukan migrasi, restore, deploy, atau edit file.

### Deliverable
- Baseline report berisi branch/commit/status Git.
- Peta arsitektur dan alur yang benar-benar ditemukan.
- Temuan dengan path/baris, bukti, dampak, tingkat risiko, dan status verifikasi.
- Daftar file yang kemungkinan terdampak per fase.
- Daftar pertanyaan bisnis yang belum terjawab.

### Gate 0
Pemilik proyek menerima baseline dan menyetujui scope perbaikan berikutnya. Tidak ada coding sebelum gate ini.

## Fase 1 — Security & data integrity

### Aktivitas
- [ ] Verifikasi akses panel Filament dan otorisasi setiap resource.
- [ ] Audit akun demo, kredensial bawaan, seeder, dan pembuatan admin produksi.
- [ ] Audit validasi request, mass assignment, rate limiting, CSRF, dan upload.
- [ ] Audit foreign key/cascade delete dan perlindungan histori inquiry/order.
- [ ] Audit privasi data kontak serta resep optik.
- [ ] Audit appointment, termasuk validasi tanggal, field kondisional, dan akses data.

### Deliverable
- Matriks role × resource/action.
- Daftar risiko P0/P1 terkonfirmasi dan mitigasi.
- Test plan keamanan dan integritas.

### Gate 1
Risiko kritis ditangani atau memiliki mitigasi yang secara eksplisit disetujui sebelum fitur inquiry dibangun.

## Fase 2 — Desain domain inquiry dan harga

### Aktivitas
- [ ] Tentukan field minimum inquiry dan item.
- [ ] Tentukan referensi inquiry yang unik dan tidak mudah ditebak.
- [ ] Tentukan snapshot nama/SKU/varian/harga serta relasi histori.
- [ ] Putuskan secara eksplisit apakah harga lensa ditambahkan ke harga frame atau menjadi harga paket pengganti.
- [ ] Definisikan harga promo, pembulatan, jumlah, status stok, dan perilaku jika harga berubah.
- [ ] Tetapkan status inquiry dan transisi yang diizinkan.
- [ ] Tentukan data resep yang benar-benar diperlukan, siapa yang dapat membacanya, dan kebijakan retensi.
- [ ] Rancang transaksi database dan perlindungan retry/klik ganda.
- [ ] Rancang payload WhatsApp dan penanganan jika browser memblokir popup.

### Deliverable
- Diagram alur inquiry.
- Usulan skema/migrasi dan dampak terhadap data lama.
- Matriks validasi dan aturan kalkulasi.
- Acceptance criteria dan test cases.

### Gate 2
Pemilik proyek menyetujui desain sebelum file migration/model/controller/view diubah.

## Fase 3 — Implementasi inquiry tersimpan dan kalkulasi server-side

### Aktivitas
- [ ] Tambahkan struktur database yang disetujui melalui migrasi non-destruktif.
- [ ] Implementasikan validasi dan kalkulasi harga di server.
- [ ] Simpan inquiry dan item dalam transaksi database.
- [ ] Buat kode inquiry dan respons sukses hanya setelah commit berhasil.
- [ ] Buka WhatsApp setelah inquiry berhasil disimpan.
- [ ] Tampilkan referensi dan ringkasan yang konsisten.
- [ ] Tambahkan otorisasi serta tampilan admin untuk inquiry.
- [ ] Tambahkan rate limit/deduplikasi yang sesuai desain.

### Tes wajib
- [ ] Penyimpanan berhasil dan gagal.
- [ ] Manipulasi harga dari browser.
- [ ] Harga lensa termasuk ke total dengan aturan yang disepakati.
- [ ] Produk/varian/lensa nonaktif dan stok habis.
- [ ] Perubahan stok/harga di antara pemilihan dan submit.
- [ ] Klik ganda/retry dan kode inquiry unik.
- [ ] Payload WhatsApp dan akses data inquiry.

### Gate 3
Tes fitur dan regresi lulus; diff ditinjau; tidak ada klaim sukses ketika transaksi database gagal.

## Fase 4 — Backup hosting + salinan cloud dan recovery

### Aktivitas
- [ ] Verifikasi platform, database, batasan hosting, dan mekanisme command yang tersedia.
- [ ] Hilangkan ketergantungan pada path executable Windows jika target Linux.
- [ ] Rancang backup database dan file upload.
- [ ] Validasi input/path dan hindari command injection.
- [ ] Periksa exit code, ukuran, checksum/integritas, dan hasil upload.
- [ ] Konfigurasikan cloud dengan kredensial least-privilege yang tidak disimpan di Git.
- [ ] Tetapkan jadwal, retensi, enkripsi, notifikasi, RPO, dan RTO.
- [ ] Tambahkan proteksi restore, snapshot sebelum restore, dan audit trail.
- [ ] Lakukan restore drill di staging/disposable environment.

### Gate 4
Restore berhasil dibuktikan di lingkungan terpisah, termasuk file upload. Backup yang hanya berhasil dibuat tetapi belum pernah diuji restore belum dianggap memenuhi gate.

## Fase 5 — Katalog dan operasional admin

### Aktivitas
- [ ] Filter varian nonaktif dari pilihan publik.
- [ ] Pastikan stok habis tidak dapat dipilih atau dipesan melalui request langsung.
- [ ] Samakan cara membentuk URL gambar varian dan banner dengan konfigurasi storage.
- [ ] Validasi harga, harga promo, SKU, slug, stok, dan status publikasi.
- [ ] Tambahkan pagination/eager loading jika sesuai volume katalog.
- [ ] Rapikan alur admin inquiry, status, serta audit perubahan.
- [ ] Validasi pengaturan WhatsApp, URL sosial, banner, dan konfigurasi toko.

### Gate 5
Pengujian katalog/admin dan pemeriksaan mobile/regresi lulus.

## Fase 6 — QA, dokumentasi, dan staging

### Aktivitas
- [ ] Jalankan syntax/lint checks dan test suite.
- [ ] Jalankan pengujian authorization, inquiry, harga, appointment, upload, backup, dan restore.
- [ ] Lakukan smoke test katalog → inquiry → referensi → WhatsApp → admin.
- [ ] Verifikasi konfigurasi staging dan filesystem/storage.
- [ ] Perbarui README dan dokumen blueprint agar sesuai dengan aplikasi nyata.
- [ ] Periksa dependency lockfile tanpa upgrade yang tidak disetujui.
- [ ] Lakukan review diff independen dan catat test yang tidak bisa dijalankan.

### Gate 6
Tidak ada kegagalan test kritis; risiko tersisa dan keterbatasan dinyatakan dengan jelas; pemilik proyek menyetujui rilis.

## Fase 7 — Produksi dan pemantauan

### Checklist
- [ ] Backup terbaru diverifikasi sebelum deployment.
- [ ] `APP_DEBUG=false`, HTTPS, secrets, permission, dan document root benar.
- [ ] Prosedur migrasi dan rollback ditinjau.
- [ ] Smoke test produksi tidak membuat data palsu yang tidak diinginkan.
- [ ] Verifikasi inquiry dan pesan WhatsApp tanpa mengekspos data sensitif.
- [ ] Verifikasi backup terjadwal dan notifikasi kegagalan.
- [ ] Catat commit rilis, hasil tes, waktu deployment, dan hasil rollback readiness.

### Gate 7
Rilis hanya setelah persetujuan eksplisit. Commit/push/deploy bukan tindakan otomatis.

## Aturan risiko dan perubahan

- **P0:** kemungkinan akses tanpa izin, kehilangan/kebocoran data besar, atau restore yang dapat merusak produksi; hentikan fitur baru sampai risiko dipahami dan dimitigasi.
- **P1:** integritas inquiry/harga, kontrol akses data, atau backup/recovery tidak andal.
- **P2:** UX, konsistensi katalog, performa, dan dokumentasi yang tidak menimbulkan risiko kritis langsung.
- Temuan yang belum diperiksa ulang diberi label *belum terverifikasi*, bukan dinyatakan sebagai bug pasti.
- Tidak menjalankan `git clean`, `git reset --hard`, restore massal, atau migrasi destruktif.
- Setiap fase mempunyai scope file, rencana tes, hasil, dan gerbang persetujuan sendiri.

## Laporan penutup tiap fase

Setiap laporan harus memuat:
1. Baseline/commit awal dan akhir.
2. File yang berubah beserta alasan.
3. Tes yang dijalankan dan hasil aktual.
4. Temuan yang terselesaikan dan yang tersisa.
5. Risiko regresi dan langkah rollback.
6. Status gate: lulus, gagal, atau menunggu persetujuan.
