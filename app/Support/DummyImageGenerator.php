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
     * Hasilkan satu gambar banner promo lebar (1680x560) ke disk publik.
     * Tiga suasana emerald/emas bergantian menurut indeks.
     */
    public function generateBanner(int $index, string $title): string
    {
        $scenes = [
            [[67, 20, 7], [234, 88, 12], [251, 191, 36]],
            [[24, 24, 27], [63, 63, 70], [245, 158, 11]],
            [[124, 45, 18], [249, 115, 22], [255, 255, 255]],
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
        $band = imagecolorallocatealpha($img, 67, 20, 7, 18);
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
