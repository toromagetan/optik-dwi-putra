<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Pembuat gambar produk dummy (untuk data contoh): menggambar ilustrasi
 * kacamata elegan dengan GD — 3 tampang per produk (depan, samping, detail).
 * Berkas asli hasil unggahan admin tentu menggantikan gambar dummy ini.
 */
class DummyImageGenerator
{
    private const W = 1200;

    private const H = 900;

    private ?string $font = null;

    public function __construct()
    {
        foreach ([
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ] as $candidate) {
            if (file_exists($candidate)) {
                $this->font = $candidate;
                break;
            }
        }
    }

    /**
     * Hasilkan 3 gambar untuk satu produk ke disk publik.
     *
     * @param  array{bg: array{int,int,int}, frame: array{int,int,int}}  $palette
     * @return array<int, array{path: string, alt: string}>
     */
    public function generateFor(int $productId, string $productName, string $sku, array $palette): array
    {
        $views = [
            ['key' => 'depan', 'label' => 'Tampak Depan'],
            ['key' => 'samping', 'label' => 'Tampak Samping'],
            ['key' => 'detail', 'label' => 'Detail Material'],
        ];

        $results = [];
        foreach ($views as $i => $view) {
            $img = $this->canvas($palette['bg']);
            $this->{'draw'.ucfirst($view['key'])}($img, $palette['frame']);
            $this->labelBand($img, $productName, $view['label'], $sku);

            ob_start();
            imagejpeg($img, null, 88);
            $binary = (string) ob_get_clean();
            imagedestroy($img);

            $path = "products/{$productId}/dummy-".($i + 1)."-{$view['key']}.jpg";
            Storage::disk('public')->put($path, $binary);

            $results[] = ['path' => $path, 'alt' => "{$productName} — {$view['label']}"];
        }

        return $results;
    }

    /**
     * Gambar "tangkapan layar" ulasan CONTOH untuk seeder testimoni:
     * 'wa' = gaya chat WhatsApp, 'google' = gaya kartu ulasan Google.
     * Selalu berlabel dummy; admin menggantinya dengan tangkapan layar
     * asli pelanggan lewat panel Ulasan Pelanggan.
     */
    public function generateReviewShot(string $kind, string $name): string
    {
        $path = $kind === 'google'
            ? "reviews/contoh-google-{$this->slug($name)}.jpg"
            : "reviews/contoh-wa-{$this->slug($name)}.jpg";
        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        $img = $kind === 'google' ? $this->reviewShotGoogle($name) : $this->reviewShotWa($name);

        ob_start();
        imagejpeg($img, null, 86);
        $binary = (string) ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function reviewShotWa(string $name): \GdImage
    {
        $w = 750;
        $h = 1050;
        $img = imagecreatetruecolor($w, $h);
        imagefilledrectangle($img, 0, 0, $w, $h, imagecolorallocate($img, 236, 229, 221));
        imagefilledrectangle($img, 0, 0, $w, 108, imagecolorallocate($img, 7, 94, 84));
        $white = imagecolorallocate($img, 255, 255, 255);
        $this->filledCircle($img, 56, 54, 34, imagecolorallocate($img, 176, 190, 197));
        $this->ttf($img, $this->initialOf($name), 26, 56, 64, $white, true);
        $this->ttf($img, $name, 25, 108, 50, $white);
        $this->ttf($img, 'online', 19, 108, 84, imagecolorallocate($img, 200, 230, 201));

        $dark = imagecolorallocate($img, 38, 38, 38);
        $gray = imagecolorallocate($img, 130, 130, 130);
        $bubbleIn = imagecolorallocate($img, 255, 255, 255);
        $bubbleOut = imagecolorallocate($img, 220, 248, 198);

        $y = 150;
        $y = $this->chatBubble($img, 28, $y, 545, $bubbleIn, $dark, $gray,
            'Kak, kacamatanya sudah sampai kemarin. Ringan banget dipakainya, pusingku berkurang banyak. Makasih banyak ya kak!', '09.41');
        $y += 26;
        $y = $this->chatBubble($img, $w - 28 - 520, $y, 520, $bubbleOut, $dark, $gray,
            'Alhamdulillah, terima kasih kak. Semoga awet dan nyaman dipakai terus. Kalau ada keluhan, jangan sungkan chat kami lagi.', '09.43');
        $y += 26;
        $this->chatBubble($img, 28, $y, 470, $bubbleIn, $dark, $gray,
            'Pasti aku rekomendasikan ke teman-teman kantor. Pelayanannya ramah banget.', '09.44');

        $this->dummyBandBottom($img, $w, $h);

        return $img;
    }

    private function reviewShotGoogle(string $name): \GdImage
    {
        $w = 750;
        $h = 1000;
        $img = imagecreatetruecolor($w, $h);
        imagefilledrectangle($img, 0, 0, $w, $h, imagecolorallocate($img, 243, 244, 246));
        $this->roundedRectPath($img, 36, 60, $w - 36, $h - 60, 28, imagecolorallocate($img, 255, 255, 255));

        $dark = imagecolorallocate($img, 32, 33, 36);
        $gray = imagecolorallocate($img, 95, 99, 104);
        $this->filledCircle($img, 116, 150, 40, imagecolorallocate($img, 30, 136, 229));
        $this->ttf($img, $this->initialOf($name), 30, 116, 161, imagecolorallocate($img, 255, 255, 255), true);
        $this->ttf($img, $name, 27, 176, 140, $dark);
        $this->ttf($img, '2 minggu lalu - Google', 20, 176, 176, $gray);
        $this->ttf($img, '★★★★★', 40, 84, 256, imagecolorallocate($img, 245, 158, 11));
        $lines = $this->wrapLines('Sudah langganan di sini dari 2023. Pelayanannya ramah, periksa matanya teliti, frame-nya awet. Terakhir ambil lensa photochromic - hasilnya rapi dan selesai lebih cepat dari janji. Sangat direkomendasikan!', 24, 560);
        $y = 316;
        foreach ($lines as $line) {
            $this->ttf($img, $line, 24, 84, $y, $dark);
            $y += 38;
        }
        $this->ttf($img, 'Membantu (12)', 21, 84, $y + 26, $gray);

        $this->dummyBandBottom($img, $w, $h);

        return $img;
    }

    /** Balon chat: gambar + teks terbungkus + jam; kembalikan y bawah. */
    private function chatBubble($img, int $x, int $y, int $bw, $bg, $dark, $gray, string $text, string $time): int
    {
        $lines = $this->wrapLines($text, 24, $bw - 44);
        $bh = 30 + count($lines) * 34 + 30;
        $this->roundedRectPath($img, $x, $y, $x + $bw, $y + $bh, 16, $bg);
        $ty = $y + 44;
        foreach ($lines as $line) {
            $this->ttf($img, $line, 24, $x + 22, $ty, $dark);
            $ty += 34;
        }
        $tw = $this->textWidth($time, 18);
        $this->ttf($img, $time, 18, $x + $bw - 22 - $tw, $y + $bh - 14, $gray);

        return $y + $bh;
    }

    private function dummyBandBottom($img, int $w, int $h): void
    {
        $band = imagecolorallocatealpha($img, 26, 46, 5, 22);
        imagefilledrectangle($img, 0, $h - 52, $w, $h, $band);
        $this->ttf($img, 'Contoh tangkapan layar (dummy)', 20, 28, $h - 18, imagecolorallocate($img, 255, 255, 255));
    }

    private function wrapLines(string $text, int $size, int $maxWidth): array
    {
        if (! $this->font) {
            return str_split($text, 42);
        }
        $lines = [];
        $line = '';
        foreach (explode(' ', $text) as $word) {
            $test = $line === '' ? $word : $line.' '.$word;
            if ($this->textWidth($test, $size) > $maxWidth && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $test;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    private function textWidth(string $text, int $size): int
    {
        if (! $this->font) {
            return strlen($text) * $size;
        }
        $box = imagettfbbox($size, 0, $this->font, $text);

        return (int) ($box[2] - $box[0]);
    }

    private function filledCircle($img, int $cx, int $cy, int $r, $color): void
    {
        imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $color);
    }

    private function roundedRectPath($img, int $x1, int $y1, int $x2, int $y2, int $radius, $color): void
    {
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        foreach ([[$x1 + $radius, $y1 + $radius], [$x2 - $radius, $y1 + $radius], [$x1 + $radius, $y2 - $radius], [$x2 - $radius, $y2 - $radius]] as [$cx, $cy]) {
            imagefilledellipse($img, $cx, $cy, $radius * 2, $radius * 2, $color);
        }
    }

    private function ttf($img, string $text, int $size, int $x, int $y, $color, bool $center = false): void
    {
        if ($this->font) {
            if ($center) {
                $x -= (int) ($this->textWidth($text, $size) / 2);
            }
            imagettftext($img, $size, 0, $x, $y, $color, $this->font, $text);
        } else {
            imagestring($img, 5, $x, $y - 14, $text, $color);
        }
    }

    private function initialOf(string $name): string
    {
        return strtoupper(substr($name, 0, 1));
    }

    private function slug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    }

    /**
     * Hasilkan satu gambar banner promo lebar (1680x560) ke disk publik.
     * Tiga suasana emerald/emas bergantian menurut indeks.
     */
    public function generateBanner(int $index, string $title): string
    {
        $scenes = [
            [[26, 46, 5], [132, 204, 22], [255, 255, 255]],
            [[17, 24, 39], [55, 65, 81], [132, 204, 22]],
            [[247, 254, 231], [163, 230, 53], [26, 46, 5]],
        ];
        [$from, $to, $frameRgb] = $scenes[$index % count($scenes)];

        $w = 1680;
        $h = 560;
        $img = imagecreatetruecolor($w, $h);
        for ($x = 0; $x < $w; $x++) {
            $t = $x / $w;
            $color = imagecolorallocate($img,
                (int) ($from[0] + ($to[0] - $from[0]) * $t),
                (int) ($from[1] + ($to[1] - $from[1]) * $t),
                (int) ($from[2] + ($to[2] - $from[2]) * $t));
            imageline($img, $x, 0, $x, $h, $color);
        }
        // Lingkaran dekoratif tembus pandang.
        $glow = imagecolorallocatealpha($img, 255, 255, 255, 112);
        imagefilledellipse($img, 1470, -60, 520, 520, $glow);
        imagefilledellipse($img, 120, 640, 420, 420, $glow);

        // Ilustrasi kacamata besar di sisi kanan.
        $c = $this->colors($img, $frameRgb);
        imagesetthickness($img, 18);
        foreach ([[930, 160], [1300, 160]] as [$x, $y]) {
            $this->filledRoundedRect($img, $x, $y, $x + 300, $y + 230, 64, $c['lens']);
            $this->outlineRoundedRect($img, $x, $y, $x + 300, $y + 230, 64, $c['frame']);
            imagefilledpolygon($img, [$x + 46, $y + 190, $x + 150, $y + 34, $x + 205, $y + 34, $x + 100, $y + 190], $c['shine']);
        }
        imagearc($img, 1115, 195, 100, 66, 180, 360, $c['frame']);
        imageline($img, 930, 195, 810, 160, $c['frame']);
        imageline($img, 1600, 195, 1660, 170, $c['frame']);

        // Label dummy kecil di kanan bawah.
        $white = imagecolorallocatealpha($img, 255, 255, 255, 30);
        if ($this->font) {
            imagettftext($img, 17, 0, 1300, 498, $white, $this->font, 'Foto contoh (dummy)');
        }

        ob_start();
        imagejpeg($img, null, 88);
        $binary = (string) ob_get_clean();
        imagedestroy($img);

        $path = 'banners/dummy-'.($index + 1).'.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function canvas(array $bg)
    {
        $img = imagecreatetruecolor(self::W, self::H);
        // Gradasi vertikal lembut dari warna latar ke putih.
        for ($y = 0; $y < self::H; $y++) {
            $t = $y / self::H;
            $r = (int) ($bg[0] + (252 - $bg[0]) * $t);
            $g = (int) ($bg[1] + (252 - $bg[1]) * $t);
            $b = (int) ($bg[2] + (252 - $bg[2]) * $t);
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, self::W, $y, $color);
        }
        // Garis lantai halus.
        $line = imagecolorallocatealpha($img, 120, 120, 120, 100);
        imageline($img, 0, (int) (self::H * 0.78), self::W, (int) (self::H * 0.78), $line);

        return $img;
    }

    private function colors($img, array $frame): array
    {
        return [
            'frame' => imagecolorallocate($img, $frame[0], $frame[1], $frame[2]),
            'lens' => imagecolorallocatealpha($img, 190, 225, 245, 78),
            'shine' => imagecolorallocatealpha($img, 255, 255, 255, 55),
            'dark' => imagecolorallocate($img, 40, 44, 48),
        ];
    }

    private function drawDepan($img, array $frame): void
    {
        $c = $this->colors($img, $frame);
        imagesetthickness($img, 16);

        foreach ([[250, 300], [680, 300]] as [$x, $y]) {
            $this->filledRoundedRect($img, $x, $y, $x + 270, $y + 210, 60, $c['lens']);
            $this->outlineRoundedRect($img, $x, $y, $x + 270, $y + 210, 60, $c['frame']);
            // Kilau lensa.
            imagefilledpolygon($img, [$x + 40, $y + 170, $x + 130, $y + 30, $x + 180, $y + 30, $x + 90, $y + 170], $c['shine']);
        }
        // Jembatan hidung + gagang ke tepi.
        imagearc($img, 600, 330, 90, 60, 180, 360, $c['frame']);
        imageline($img, 250, 330, 110, 290, $c['frame']);
        imageline($img, 950, 330, 1090, 290, $c['frame']);
    }

    private function drawSamping($img, array $frame): void
    {
        $c = $this->colors($img, $frame);
        imagesetthickness($img, 16);

        // Satu lensa dari samping + gagang memanjang.
        $this->filledRoundedRect($img, 210, 300, 470, 510, 55, $c['lens']);
        $this->outlineRoundedRect($img, 210, 300, 470, 510, 55, $c['frame']);
        imagefilledpolygon($img, [250, 470, 340, 330, 390, 330, 300, 470], $c['shine']);
        imageline($img, 470, 330, 1020, 300, $c['frame']);
        imageline($img, 1020, 300, 1060, 430, $c['frame']);
        // Engsel.
        imagefilledellipse($img, 478, 332, 26, 26, $c['dark']);
    }

    private function drawDetail($img, array $frame): void
    {
        $c = $this->colors($img, $frame);
        imagesetthickness($img, 26);

        // Potongan sudut frame besar + engsel.
        $this->filledRoundedRect($img, 180, 210, 900, 700, 90, $c['lens']);
        $this->outlineRoundedRect($img, 180, 210, 900, 700, 90, $c['frame']);
        imagefilledpolygon($img, [280, 620, 520, 300, 640, 300, 400, 620], $c['shine']);
        imagefilledellipse($img, 905, 260, 44, 44, $c['dark']);
        imagefilledellipse($img, 905, 260, 18, 18, $c['shine']);
        imageline($img, 900, 250, 1080, 210, $c['frame']);
    }

    /** Kotak sudut membulat terisi (GD PHP tidak menyediakan fungsi bawaannya). */
    private function filledRoundedRect($img, int $x1, int $y1, int $x2, int $y2, int $r, $color): void
    {
        imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        imagefilledarc($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, 180, 270, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, 270, 360, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, 0, 90, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, 90, 180, $color, IMG_ARC_PIE);
    }

    /** Garis tepi kotak sudut membulat. */
    private function outlineRoundedRect($img, int $x1, int $y1, int $x2, int $y2, int $r, $color): void
    {
        imageline($img, $x1 + $r, $y1, $x2 - $r, $y1, $color);
        imageline($img, $x1 + $r, $y2, $x2 - $r, $y2, $color);
        imageline($img, $x1, $y1 + $r, $x1, $y2 - $r, $color);
        imageline($img, $x2, $y1 + $r, $x2, $y2 - $r, $color);
        imagearc($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, 180, 270, $color);
        imagearc($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, 270, 360, $color);
        imagearc($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, 0, 90, $color);
        imagearc($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, 90, 180, $color);
    }

    private function labelBand($img, string $name, string $viewLabel, string $sku): void
    {
        $band = imagecolorallocatealpha($img, 26, 46, 5, 18);
        imagefilledrectangle($img, 0, self::H - 108, self::W, self::H, $band);
        $white = imagecolorallocate($img, 255, 255, 255);
        $soft = imagecolorallocate($img, 190, 230, 215);

        if ($this->font) {
            imagettftext($img, 30, 0, 44, self::H - 58, $white, $this->font, $name);
            imagettftext($img, 21, 0, 44, self::H - 22, $soft, $this->font, "{$viewLabel} · SKU {$sku} · Foto contoh (dummy)");
        } else {
            imagestring($img, 5, 44, self::H - 70, $name, $white);
            imagestring($img, 4, 44, self::H - 40, "{$viewLabel} - SKU {$sku} - dummy", $soft);
        }
    }
}
