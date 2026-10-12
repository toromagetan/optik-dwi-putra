<?php

namespace App\Support;

/**
 * Konten artikel bantuan (footer "Layanan & Bantuan").
 * Ditulis sebagai data statis agar mudah dirawat tanpa migrasi database.
 * Body memakai HTML sederhana (h3, p, ul, table) yang distyle oleh view artikel.
 */
class Articles
{
    public static function all(): array
    {
        return [
            'cara-membaca-resep-mata' => [
                'title' => 'Cara Membaca Resep Mata',
                'excerpt' => 'Panduan memahami singkatan pada resep kacamata Anda: SPH, CYL, AXIS, ADD, dan PD — untuk mata kanan (OD) dan kiri (OS).',
                'body' => <<<'HTML'
<p>Resep kacamata adalah catatan hasil pemeriksaan mata yang ditulis dengan singkatan standar internasional. Dengan memahami artinya, Anda bisa mengisi formulir resep saat memesan kacamata secara online dengan percaya diri.</p>
<h3>OD dan OS: Kanan dan Kiri</h3>
<ul>
<li><strong>OD (Oculus Dexter)</strong> — mata kanan.</li>
<li><strong>OS (Oculus Sinister)</strong> — mata kiri.</li>
</ul>
<p>Kedua mata hampir selalu memiliki ukuran berbeda, jadi jangan tertukar saat mengisi formulir.</p>
<h3>SPH (Sphere) — Ukuran Minus atau Plus</h3>
<p>SPH menunjukkan kekuatan lensa utama. Tanda <strong>minus (−)</strong> berarti rabun jauh (miopia), tanda <strong>plus (+)</strong> berarti rabun dekat (hipermetropia). Semakin besar angkanya, semakin kuat koreksi yang dibutuhkan. Nilai 0.00 disebut <em>plano</em> (tidak ada koreksi).</p>
<h3>CYL (Cylinder) dan AXIS — Koreksi Silinder</h3>
<p>CYL adalah ukuran untuk mata silinder (astigmatisme), biasanya ditulis dengan tanda minus. CYL selalu berpasangan dengan <strong>AXIS</strong>, yaitu arah kemiringan silinder dalam derajat 1 sampai 180. Jika kolom CYL kosong atau 0.00, berarti tidak ada silinder dan AXIS tidak perlu diisi.</p>
<h3>ADD (Addition) — Tambahan untuk Membaca</h3>
<p>ADD adalah kekuatan tambahan pada bagian bawah lensa untuk membantu melihat dekat, umumnya dibutuhkan mulai usia 40 tahun (presbiopia). Nilainya selalu plus, berkisar +0.75 sampai +3.00, dan biasanya sama untuk kedua mata.</p>
<h3>PD (Pupillary Distance) — Jarak Antar Pupil</h3>
<p>PD adalah jarak antara kedua pupil dalam milimeter (umumnya 54–74 mm pada orang dewasa). Angka ini menentukan titik fokus lensa dipasang tepat di depan mata Anda, jadi PD yang akurat membuat kacamata nyaman dipakai dan tidak pusing.</p>
<h3>Contoh Resep</h3>
<table>
<thead><tr><th>Mata</th><th>SPH</th><th>CYL</th><th>AXIS</th><th>ADD</th><th>PD</th></tr></thead>
<tbody>
<tr><td>OD (Kanan)</td><td>−1.50</td><td>−0.50</td><td>180</td><td>+1.50</td><td>63</td></tr>
<tr><td>OS (Kiri)</td><td>−1.25</td><td>−0.75</td><td>175</td><td>+1.50</td><td>63</td></tr>
</tbody>
</table>
<p>Contoh di atas dibaca: mata kanan minus 1.50 dengan silinder 0.50 pada sumbu 180, mata kiri minus 1.25 dengan silinder 0.75 pada sumbu 175, keduanya membutuhkan tambahan baca +1.50.</p>
<p>Belum punya resep terbaru? Ukuran mata berubah seiring waktu — periksakan mata Anda secara gratis di Optik Dwi Putra sebelum memesan, tanpa kewajiban membeli.</p>
HTML,
            ],
            'panduan-ukuran-frame' => [
                'title' => 'Panduan Ukuran Frame',
                'excerpt' => 'Cara membaca tiga angka ukuran pada gagang kacamata dan memilih frame yang pas untuk lebar wajah Anda.',
                'body' => <<<'HTML'
<p>Frame yang pas bukan hanya soal gaya — frame yang terlalu sempit menekan pelipis, yang terlalu longgar melorot saat Anda menunduk. Kabar baiknya, setiap kacamata membawa "kartu identitas" ukurannya sendiri.</p>
<h3>Tiga Angka pada Gagang Kacamata</h3>
<p>Di sisi dalam gagang (temple) hampir semua kacamata tercetak tiga angka, contohnya <strong>52 □ 18 − 140</strong>:</p>
<ul>
<li><strong>52</strong> — lebar lensa dalam milimeter (satu mata).</li>
<li><strong>18</strong> — lebar jembatan hidung (bridge) dalam milimeter.</li>
<li><strong>140</strong> — panjang gagang dalam milimeter.</li>
</ul>
<p>Cara termudah memilih ukuran online: baca angka pada kacamata lama Anda yang nyaman, lalu cari frame dengan angka yang sama atau selisih 1–2 mm.</p>
<h3>Panduan Lebar Wajah</h3>
<table>
<thead><tr><th>Ukuran</th><th>Lebar Lensa</th><th>Cocok untuk Lebar Wajah</th></tr></thead>
<tbody>
<tr><td>S (Kecil)</td><td>≤ 49 mm</td><td>≤ 130 mm</td></tr>
<tr><td>M (Sedang)</td><td>50–53 mm</td><td>131–140 mm</td></tr>
<tr><td>L (Besar)</td><td>≥ 54 mm</td><td>≥ 141 mm</td></tr>
</tbody>
</table>
<p>Lebar wajah diukur dari pelipis ke pelipis dengan penggaris saat bercermin. Jika ragu di antara dua ukuran, pilih yang lebih besar — sedikit longgar masih nyaman, terlalu sempit tidak.</p>
<h3>Tips Memilih Bentuk Frame</h3>
<ul>
<li>Wajah oval: hampir semua bentuk cocok, termasuk aviator dan persegi.</li>
<li>Wajah bulat: frame persegi atau persegi panjang memberi kesan tegas.</li>
<li>Wajah persegi: frame bulat atau oval melembutkan garis rahang.</li>
<li>Wajah hati: frame yang lebih lebar di bagian bawah atau model cat-eye ringan.</li>
</ul>
<p>Untuk wajah Asia, frame dengan <em>nose pad</em> yang dapat diatur atau desain Asian Fit membantu frame tidak melorot dan tidak menyentuh pipi. Tim kami siap membantu memilihkan lewat WhatsApp — kirim foto wajah tampak depan, kami rekomendasikan ukurannya.</p>
HTML,
            ],
            'kebijakan-pengiriman-retur' => [
                'title' => 'Kebijakan Pengiriman & Retur',
                'excerpt' => 'Waktu pengerjaan lensa resep, pengiriman, dan ketentuan retur untuk frame maupun kacamata resep yang dibuat khusus untuk Anda.',
                'body' => <<<'HTML'
<h3>Waktu Pengerjaan</h3>
<ul>
<li><strong>Frame Only (tanpa lensa resep)</strong> — dikirim maksimal 1×24 jam setelah pembayaran terkonfirmasi (hari kerja).</li>
<li><strong>Lensa Single Vision stok</strong> — pengerjaan 2–5 hari kerja, karena lensa dipotong dan dipasang mengikuti resep dan PD Anda.</li>
<li><strong>Lensa khusus</strong> (progresif, photochromic, index tinggi, atau resep di luar stok) — 7–14 hari kerja; estimasi pasti kami sampaikan sebelum pembayaran.</li>
</ul>
<h3>Pengiriman</h3>
<p>Setiap kacamata dikirim dalam kotak keras (hard case) dengan kain microfiber, dibungkus bubble wrap dan kardus. Nomor resi kami kirimkan melalui WhatsApp begitu paket diserahkan ke kurir. Periksa paket saat diterima; bila kemasan rusak berat, foto sebelum dibuka dan hubungi kami di hari yang sama.</p>
<h3>Ketentuan Retur</h3>
<ul>
<li><strong>Frame Only</strong> dapat ditukar atau dikembalikan maksimal 7 hari sejak diterima, dalam keadaan baru, belum dipakai di luar ruangan, dan lengkap dengan aksesori. Ongkos kirim pengembalian ditanggung pembeli kecuali kesalahan dari kami.</li>
<li><strong>Lensa resep bersifat personal</strong> — dipotong khusus mengikuti resep dan ukuran mata Anda, sehingga tidak dapat dikembalikan atau ditukar karena alasan berubah pikiran. Ini standar yang berlaku di seluruh industri optik.</li>
<li><strong>Kesalahan resep dari pihak kami</strong> (hasil jadi tidak sesuai resep yang Anda kirimkan) kami perbaiki dengan pembuatan ulang lensa tanpa biaya, maksimal klaim 14 hari sejak diterima. Sertakan foto resep asli saat mengajukan klaim.</li>
<li><strong>Cacat produksi</strong> (frame patah di sambungan, lapisan lensa mengelupas) ditangani melalui garansi layanan — baca halaman Garansi 6 Bulan.</li>
</ul>
<p>Ragu sebelum memesan? Konsultasikan resep dan pilihan frame Anda lewat WhatsApp terlebih dahulu. Kami lebih senang Anda yakin sebelum lensa dipotong.</p>
HTML,
            ],
            'garansi-layanan' => [
                'title' => 'Garansi 6 Bulan',
                'excerpt' => 'Setiap kacamata dari Optik Dwi Putra dilindungi garansi layanan 6 bulan: penyetelan gratis dan perlindungan cacat produksi.',
                'body' => <<<'HTML'
<p>Kacamata adalah alat bantu yang Anda pakai setiap hari, jadi perlindungannya tidak berhenti di kasir. Setiap pembelian frame beserta lensa di Optik Dwi Putra dilindungi <strong>garansi layanan selama 6 bulan</strong> sejak tanggal pembelian.</p>
<h3>Yang Ditanggung</h3>
<ul>
<li><strong>Penyetelan gratis tanpa batas kunjungan</strong> — frame miring, gagang longgar, nose pad tidak nyaman: datang saja, kami setelkan.</li>
<li><strong>Cacat produksi frame</strong> — patah pada sambungan atau engsel, cat mengelupas bukan karena gesekan, dan cacat material lain dari pabrik: diperbaiki atau diganti sesuai ketersediaan model.</li>
<li><strong>Cacat produksi lensa</strong> — lapisan (coating) mengelupas atau bergelembung tanpa sebab benturan: lensa dibuat ulang dengan resep yang sama.</li>
<li><strong>Penggantian sekrup dan nose pad standar</strong> yang hilang atau aus selama masa garansi.</li>
</ul>
<h3>Yang Tidak Ditanggung</h3>
<ul>
<li>Kehilangan kacamata.</li>
<li>Kerusakan akibat benturan, terinjak, terduduki, atau kecelakaan lain.</li>
<li>Goresan pada lensa akibat pemakaian dan perawatan sehari-hari.</li>
<li>Perubahan ukuran resep mata Anda selama masa garansi.</li>
</ul>
<h3>Cara Klaim</h3>
<ul>
<li>Hubungi kami melalui WhatsApp dengan nomor order atau nama pembeli, dan ceritakan keluhannya — foto bagian yang bermasalah akan sangat membantu penilaian awal.</li>
<li>Untuk pelanggan luar kota, kacamata dapat dikirimkan ke alamat optik kami; ongkos kirim pengiriman ditanggung pembeli, pengiriman kembali setelah perbaikan kami tanggung.</li>
<li>Penilaian klaim maksimal 3 hari kerja sejak kacamata kami terima. Keputusan perbaikan atau penggantian mengikuti hasil pemeriksaan teknisi.</li>
</ul>
<p>Di luar masa garansi pun Anda tetap pelanggan kami: penyetelan ringan selalu gratis untuk kacamata yang dibeli di Optik Dwi Putra.</p>
HTML,
            ],
        ];
    }
}
