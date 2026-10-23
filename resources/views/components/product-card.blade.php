@props(['product'])
@php($availability = $product->effectiveAvailability())
<article class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
    <a href="{{ route('product.show', $product->slug) }}" class="relative block aspect-[4/3] overflow-hidden bg-stone-100">
        @if($product->mainImage())
            <img src="{{ $product->mainImage()->url() }}" alt="{{ $product->mainImage()->alt_text ?? $product->name }}" loading="lazy"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-stone-300">
                <svg class="h-14 w-14" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
            </div>
        @endif
        @if($product->is_featured)
            <span class="absolute left-3 top-3 rounded-full bg-gold-500 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide text-white shadow">Unggulan</span>
        @endif
        <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold shadow-sm
            {{ $availability === 'available' ? 'bg-brand-600 text-white' : ($availability === 'contact_for_availability' ? 'bg-amber-500 text-white' : 'bg-red-600 text-white') }}">
            {{ $availability === 'available' ? 'Tersedia' : ($availability === 'contact_for_availability' ? 'Tanya Dulu' : 'Stok Habis') }}
        </span>
    </a>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-stone-400">{{ $product->brand?->name ?? $product->category?->name }}</p>
        <a href="{{ route('product.show', $product->slug) }}" class="mt-1 line-clamp-2 font-bold leading-snug text-brand-950 transition hover:text-brand-700">{{ $product->name }}</a>
        <p class="mt-auto pt-3 text-[17px] font-extrabold text-brand-700">{{ $product->formattedPrice() }}</p>
        <button type="button" @if($availability === 'out_of_stock') disabled @endif
            onclick="addToCart({{ $product->id }}, 1, @js($product->name))"
            class="mt-3 w-full rounded-xl py-2.5 text-sm font-bold transition {{ $availability === 'out_of_stock' ? 'cursor-not-allowed bg-stone-100 text-stone-400' : 'bg-brand-700 text-white hover:bg-brand-800' }}">
            {{ $availability === 'out_of_stock' ? 'Stok Habis' : '+ Keranjang' }}
        </button>
    </div>
</article>
