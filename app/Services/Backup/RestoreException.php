<?php

namespace App\Services\Backup;

use RuntimeException;

/** Kesalahan restore yang aman ditampilkan ke admin (pesan terkurasi). */
class RestoreException extends RuntimeException {}
