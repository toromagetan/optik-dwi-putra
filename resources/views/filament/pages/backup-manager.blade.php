<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-lime-100 text-lime-600 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Enkripsi Militer Aktif</h3>
                    <p class="text-sm text-gray-500">AES-256-GCM Protocol</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Data Anda dilindungi enkripsi kelas atas. Bahkan jika server dibobol, data pelanggan tidak dapat dibaca tanpa Master Key.
            </p>
            <x-filament::button wire:click="createBackup" size="lg" color="success">
                Buat Cadangan Baru
            </x-filament::button>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold mb-4">Riwayat Cadangan</h3>
            
            @if(count($backups) === 0)
                <p class="text-gray-500 italic text-sm">Belum ada file backup.</p>
            @else
                <div class="space-y-4">
                    @foreach($backups as $b)
                        <div class="flex items-center justify-between p-3 border border-gray-100 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $b['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $b['time'] }} &bull; {{ $b['size'] }}</p>
                            </div>
                            <x-filament::button size="sm" color="danger" wire:click="restoreBackup('{{ $b['name'] }}')" wire:confirm="PERINGATAN BAHAYA!

Seluruh data pelanggan, order, dan produk yang masuk SETELAH tanggal ini akan TERHAPUS PERMANEN dan kembali ke titik backup ini.

Apakah Anda benar-benar yakin ingin melakukan Restore?">
                                Pulihkan
                            </x-filament::button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
