@extends('layouts.admin')
@section('title', 'Pengaturan Toko')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100')
@php($labelCls = 'block text-sm font-bold text-stone-700')

    <p class="mb-5 max-w-3xl text-sm leading-relaxed text-stone-500">Semua hal yang tampil di toko diatur dari halaman ini — identitas, kontak, nomor WhatsApp, tautan Telegram & media sosial, teks promosi, sampai batas kuantitas keranjang. Perubahan langsung berlaku di storefront dan tercatat di Audit Log.</p>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        @foreach($groups as $groupKey => $groupLabel)
            <section class="mb-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-extrabold text-brand-950">{{ $groupLabel }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach($registry as $key => [$label, $type, $default, $group])
                        @if($group !== $groupKey) @continue @endif
                        <div class="{{ $type === 'textarea' ? 'sm:col-span-2' : '' }}">
                            @if($type === 'image')
                                <label class="{{ $labelCls }}">{{ $label }}</label>
                                <div class="mt-2 flex items-center gap-4">
                                    @if(!empty($store['logo_url']))
                                        <img src="{{ $store['logo_url'] }}" alt="Logo saat ini" class="h-16 w-16 rounded-xl object-cover ring-1 ring-stone-200">
                                    @else
                                        <span class="flex h-16 w-16 items-center justify-center rounded-xl bg-stone-100 text-[10px] font-bold text-stone-400">BELUM ADA</span>
                                    @endif
                                    <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-700 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-brand-800">
                                </div>
                                <p class="mt-1 text-xs text-stone-400">JPG/PNG/WebP maks 1 MB. Kosongkan bila tidak ingin mengganti logo.</p>
                            @elseif($type === 'textarea')
                                <label class="{{ $labelCls }}">{{ $label }}</label>
                                <textarea name="{{ $key }}" rows="3" class="{{ $inputCls }}">{{ old($key, $values[$key]) }}</textarea>
                            @elseif($type === 'number')
                                <label class="{{ $labelCls }}">{{ $label }}</label>
                                <input type="number" name="{{ $key }}" value="{{ old($key, $values[$key]) }}" min="1" max="100" class="{{ $inputCls }}">
                            @elseif($type === 'email')
                                <label class="{{ $labelCls }}">{{ $label }}</label>
                                <input type="email" name="{{ $key }}" value="{{ old($key, $values[$key]) }}" class="{{ $inputCls }}" placeholder="halo@tokosaya.id">
                            @elseif($type === 'url')
                                <label class="{{ $labelCls }}">{{ $label }}</label>
                                <input type="url" name="{{ $key }}" value="{{ old($key, $values[$key]) }}" class="{{ $inputCls }}" placeholder="https://…">
                            @else
                                <label class="{{ $labelCls }}">{{ $label }}</label>
                                <input type="text" name="{{ $key }}" value="{{ old($key, $values[$key]) }}" class="{{ $inputCls }}"
                                    @if($key === 'whatsapp_number') placeholder="62812xxxxxxxx (hanya angka, awali 62)" @endif>
                                @if($key === 'whatsapp_number')<p class="mt-1 text-xs text-stone-400">Nomor ini menjadi tujuan tombol “Pesan via WhatsApp” di keranjang. Spasi & tanda hubung otomatis dibersihkan.</p>@endif
                                @if($key === 'promo_text')<p class="mt-1 text-xs text-stone-400">Tampil sebagai strip promosi di bagian paling atas toko. Kosongkan untuk menyembunyikan.</p>@endif
                            @endif
                            @error($key)<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-4 flex justify-end">
            <button type="submit" class="rounded-xl bg-brand-700 px-8 py-3.5 text-sm font-extrabold text-white shadow-xl transition hover:bg-brand-800">Simpan Semua Pengaturan</button>
        </div>
    </form>
@endsection
