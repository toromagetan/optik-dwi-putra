@extends('layouts.storefront')

@section('title', 'Keranjang Belanja')
@section('meta_description', 'Keranjang belanja '.($store['store_name'] ?? 'Optik Dwi Putra').' — teruskan pesanan melalui WhatsApp, Telegram, atau marketplace.')

@section('content')
<section class="mx-auto max-w-7xl px-4 pt-8 lg:px-8">
    <h1 class="text-2xl font-extrabold text-brand-950">Keranjang Belanja</h1>
    <p class="mt-1 max-w-2xl text-sm text-stone-500">Harga dan ketersediaan selalu diperiksa ulang dari data toko terbaru. Setelah yakin, teruskan pesanan melalui kanal favorit Anda — pembayaran dan konfirmasi final dilakukan langsung dengan admin toko / di marketplace.</p>

    <div id="cart-loading" class="mt-8 rounded-2xl border border-stone-200 bg-white px-6 py-12 text-center text-sm text-stone-500">Memeriksa keranjang…</div>

    <div id="cart-empty" class="mt-8 hidden rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-14 text-center">
        <p class="text-lg font-extrabold text-brand-950">Keranjang masih kosong</p>
        <p class="mt-1 text-sm text-stone-500">Jelajahi katalog dan tambahkan produk yang Anda sukai.</p>
        <a href="{{ route('catalog') }}" class="mt-5 inline-block rounded-full bg-brand-700 px-6 py-3 text-sm font-bold text-white hover:bg-brand-800">Mulai Belanja</a>
    </div>

    <div id="cart-content" class="mt-8 hidden gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div id="cart-issues" class="mb-4 hidden rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800"></div>
            <div id="cart-lines" class="space-y-4"></div>
        </div>

        <aside class="mt-8 lg:mt-0">
            <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-bold uppercase tracking-wider text-stone-400">Ringkasan</p>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-sm font-medium text-stone-500">Subtotal referensi</span>
                    <span id="cart-subtotal" class="text-2xl font-extrabold text-brand-700">Rp0</span>
                </div>
                <p class="mt-2 text-xs leading-relaxed text-stone-400">Belum termasuk ongkos kirim bila ada. Harga & stok final dikonfirmasi admin.</p>

                <div class="mt-6 space-y-3">
                    <a id="wa-button" href="#" target="_blank" rel="noopener" class="hidden w-full items-center justify-center gap-2 rounded-full bg-[#25D366] px-5 py-3.5 text-sm font-extrabold text-white shadow transition hover:brightness-95">
                        Pesan via WhatsApp
                    </a>
                    <div id="telegram-box" class="hidden rounded-3xl border border-sky-200 bg-sky-50 p-5">
                        <p class="text-sm font-bold text-sky-900">Pesan via Telegram</p>
                        <p class="mt-1 text-xs leading-relaxed text-sky-800/80">Buka Telegram toko, lalu tempel ringkasan pesanan yang tersalin.</p>
                        <div class="mt-3 flex gap-2">
                            <button type="button" id="copy-summary" class="flex-1 rounded-full bg-sky-600 px-3 py-2 text-[13px] font-bold text-white transition hover:bg-sky-700">Salin Ringkasan</button>
                            <a id="telegram-link" href="#" target="_blank" rel="noopener" class="flex-1 rounded-full border border-sky-300 bg-white px-3 py-2 text-center text-[13px] font-bold text-sky-700 transition hover:bg-sky-100">Buka Telegram ↗</a>
                        </div>
                    </div>
                    <div id="marketplace-box" class="hidden rounded-3xl border border-orange-200 bg-orange-50 p-5">
                        <p class="text-sm font-bold text-orange-900">Checkout via Marketplace</p>
                        <p class="mt-1 text-xs leading-relaxed text-orange-800/80">Marketplace tidak dapat memindahkan isi keranjang secara otomatis. Buka halaman produk satu per satu di bawah ini, lalu checkout di sana:</p>
                        <div id="marketplace-links" class="mt-3 space-y-2"></div>
                    </div>
                </div>

                <button type="button" id="clear-cart" class="mt-5 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-[13px] font-bold text-stone-500 transition hover:bg-stone-50 hover:text-red-600">Kosongkan Keranjang</button>
            </div>
        </aside>
    </div>
</section>

@push('scripts')
<script>
    const CSRF = @js(csrf_token());
    const state = { lines: [], waText: '' };

    function formatRp(n) { return 'Rp' + Number(n || 0).toLocaleString('id-ID'); }
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

    async function loadCart() {
        const items = getCart();
        const loading = document.getElementById('cart-loading');
        const empty = document.getElementById('cart-empty');
        const content = document.getElementById('cart-content');

        if (!items.length) {
            loading.classList.add('hidden'); empty.classList.remove('hidden'); content.classList.add('hidden'); content.classList.remove('grid');
            return;
        }

        loading.classList.remove('hidden'); empty.classList.add('hidden');

        try {
            const res = await fetch(@js(route('cart.validate')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ items: items })
            });
            const data = await res.json();
            state.lines = data.lines || [];
            state.waText = data.whatsapp_text || '';

            // Samakan keranjang lokal dengan hasil validasi server (qty disesuaikan / item dikeluarkan).
            setCart(state.lines.map(l => ({ product_id: l.product_id, qty: l.qty })));

            loading.classList.add('hidden');
            if (!state.lines.length) {
                empty.classList.remove('hidden');
                if ((data.issues || []).length) { showToast(data.issues[0]); }
                return;
            }

            content.classList.remove('hidden'); content.classList.add('grid');
            renderLines();
            renderIssues(data.issues || []);
            document.getElementById('cart-subtotal').textContent = data.subtotal_formatted || formatRp(data.subtotal);

            const waBtn = document.getElementById('wa-button');
            if (data.whatsapp_url) { waBtn.href = data.whatsapp_url; waBtn.classList.remove('hidden'); waBtn.classList.add('flex'); }
            else { waBtn.classList.add('hidden'); }

            const tgBox = document.getElementById('telegram-box');
            if (data.telegram_url) {
                tgBox.classList.remove('hidden');
                document.getElementById('telegram-link').href = data.telegram_url;
            } else { tgBox.classList.add('hidden'); }

            renderMarketplace();
        } catch (e) {
            loading.textContent = 'Gagal memeriksa keranjang. Muat ulang halaman untuk mencoba lagi.';
        }
    }

    function renderLines() {
        const wrap = document.getElementById('cart-lines');
        wrap.innerHTML = state.lines.map(l => `
            <div class="flex gap-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                ${l.image ? `<img src="${l.image}" alt="${escapeHtml(l.name)}" class="h-24 w-24 shrink-0 rounded-xl object-cover">` : `<div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-xl bg-stone-100 text-xs text-stone-400">Tidak ada foto</div>`}
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-bold text-brand-950">${escapeHtml(l.name)}</p>
                            <p class="text-xs text-stone-400">SKU ${escapeHtml(l.sku)}${l.brand ? ' · ' + escapeHtml(l.brand) : ''}</p>
                        </div>
                        <button type="button" onclick="removeLine(${l.product_id})" class="shrink-0 text-xs font-bold text-red-500 hover:text-red-700">Hapus</button>
                    </div>
                    ${l.availability === 'contact_for_availability' ? '<p class="mt-1 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700">Ketersediaan dikonfirmasi admin</p>' : ''}
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center rounded-lg border border-stone-300">
                            <button type="button" onclick="changeQty(${l.product_id}, -1)" class="px-3 py-1.5 font-bold text-stone-500 hover:text-brand-700">−</button>
                            <span class="w-10 text-center text-sm font-bold">${l.qty}</span>
                            <button type="button" onclick="changeQty(${l.product_id}, 1)" class="px-3 py-1.5 font-bold text-stone-500 hover:text-brand-700">+</button>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-stone-400">${l.price_formatted} × ${l.qty}</p>
                            <p class="font-extrabold text-brand-700">${l.line_total_formatted}</p>
                        </div>
                    </div>
                </div>
            </div>`).join('');
    }

    function renderIssues(issues) {
        const box = document.getElementById('cart-issues');
        if (!issues.length) { box.classList.add('hidden'); box.innerHTML = ''; return; }
        box.classList.remove('hidden');
        box.innerHTML = '<p class="font-bold">Keranjang disesuaikan dengan data terbaru:</p><ul class="mt-1 list-inside list-disc">' +
            issues.map(i => `<li>${escapeHtml(i)}</li>`).join('') + '</ul>';
    }

    function renderMarketplace() {
        const box = document.getElementById('marketplace-box');
        const wrap = document.getElementById('marketplace-links');
        const groups = [];
        state.lines.forEach(l => {
            (l.channels || []).filter(c => c.is_marketplace).forEach(c => {
                groups.push({ product: l.name, label: c.label, url: c.url });
            });
        });
        if (!groups.length) { box.classList.add('hidden'); return; }
        box.classList.remove('hidden');
        wrap.innerHTML = groups.map(g => `
            <a href="${escapeHtml(g.url)}" target="_blank" rel="noopener" class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-[13px] font-bold text-orange-700 ring-1 ring-orange-200 transition hover:bg-orange-100">
                <span class="truncate">${escapeHtml(g.product)} — ${escapeHtml(g.label)}</span><span>↗</span>
            </a>`).join('');
    }

    function changeQty(productId, delta) {
        const items = getCart();
        const found = items.find(i => i.product_id === productId);
        if (!found) return;
        found.qty = (parseInt(found.qty) || 1) + delta;
        if (found.qty <= 0) { setCart(items.filter(i => i.product_id !== productId)); }
        else { setCart(items); }
        loadCart();
    }

    function removeLine(productId) {
        setCart(getCart().filter(i => i.product_id !== productId));
        loadCart();
    }

    document.getElementById('clear-cart').addEventListener('click', () => {
        if (confirm('Kosongkan seluruh keranjang?')) { setCart([]); loadCart(); }
    });

    document.getElementById('copy-summary').addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(state.waText); showToast('Ringkasan pesanan tersalin'); }
        catch (e) { showToast('Gagal menyalin — salin manual dari pesan WhatsApp'); }
    });

    document.addEventListener('DOMContentLoaded', loadCart);
</script>
@endpush
@endsection
