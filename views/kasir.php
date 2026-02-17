<?php
// views/kasir.php
$categories = query("SELECT * FROM kategori");
$selected_kat = isset($_GET['kat']) ? $_GET['kat'] : '';

$query_products = "SELECT p.*, SUM(s.stok) as total_stok 
                   FROM produk_tarumpah p 
                   LEFT JOIN stok_produk s ON p.id_produk = s.id_produk 
                   WHERE p.status = 'aktif'";

if ($selected_kat) {
    $query_products .= " AND p.id_produk IN (SELECT id_produk FROM produk_kategori WHERE id_kategori = " . (int)$selected_kat . ")";
}

$query_products .= " GROUP BY p.id_produk";
$products = query($query_products);

// Handle Checkout Submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'checkout') {
    $cart = json_decode($_POST['cart_data'], true);
    $metode = escape($_POST['metode_pembayaran']);
    $total_harga = (float)$_POST['total_harga'];

    if (!empty($cart)) {
        $status = ($metode == 'online') ? 'diproses' : 'selesai';
        $query_trx = "INSERT INTO transaksi (total_harga, metode_pembayaran, status) VALUES ($total_harga, '$metode', '$status')";
        if (query($query_trx)) {
            $id_transaksi = lastInsertId();
            foreach ($cart as $item) {
                $id_p = (int)$item['id_produk'];
                $id_u = (int)$item['id_ukuran'];
                $qty = (int)$item['qty'];
                $price = (float)$item['price'];
                $sub = $qty * $price;

                query("INSERT INTO detail_transaksi (id_transaksi, id_produk, id_ukuran, jumlah, harga_satuan, subtotal) 
                       VALUES ($id_transaksi, $id_p, $id_u, $qty, $price, $sub)");
            }
            $message = "<script>alert('Transaksi Berhasil!'); window.location.href='?page=transaksi';</script>";
        }
    }
}
?>

<?php echo $message; ?>

<div class="flex flex-col lg:flex-row gap-8">
    <div class="flex-1 w-full space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full max-w-md">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="search_produk" placeholder="Cari Tarumpah..." class="w-full bg-white border border-gray-200 rounded-2xl py-3 pl-12 pr-4 text-sm focus:outline-none focus:border-[#3e2723] transition-all text-[#1a1a1a] shadow-sm">
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 no-scrollbar">
                <a href="?page=kasir" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?php echo !$selected_kat ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
                    Semua
                </a>
                <?php foreach($categories as $kat): ?>
                <a href="?page=kasir&kat=<?php echo $kat['id_kategori']; ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?php echo $selected_kat == $kat['id_kategori'] ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
                    <?php echo $kat['nama_kategori']; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php if (empty($products)): ?>
                <div class="col-span-full card-gradient p-10 text-center text-gray-400">
                    <i class="ph ph-mask-sad text-4xl mb-2 text-[#3e2723]"></i>
                    <p>Produk tidak tersedia.</p>
                </div>
            <?php else: foreach($products as $p): 
                $foto_url = "assets/images/produk/" . $p['foto'];
                if (!file_exists($foto_url) || empty($p['foto'])) $foto_url = "https://ui-avatars.com/api/?name=" . urlencode($p['nama_produk']) . "&background=3e2723&color=fff";
            ?>
            <div onclick="openSizeModal(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="card-gradient p-3 rounded-3xl group cursor-pointer hover:border-[#3e2723]/50 transition-all bg-white relative">
                <div class="aspect-square rounded-2xl bg-gray-50 relative overflow-hidden mb-3">
                    <img src="<?php echo $foto_url; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php $is_total_low = ($p['total_stok'] < 5); ?>
                    <div class="absolute top-2 right-2 px-2 py-1 <?php echo $is_total_low ? 'bg-rose-600' : 'bg-[#3e2723]/90'; ?> backdrop-blur-sm text-white text-[9px] font-bold rounded-lg shadow-md">
                        Stok: <?php echo $p['total_stok'] ?? 0; ?>
                    </div>
                </div>
                <h4 class="font-bold text-[11px] mb-1 line-clamp-1 text-[#1a1a1a] uppercase px-1"><?php echo $p['nama_produk']; ?></h4>
                
                <div class="flex flex-wrap gap-1 px-1 mt-2 mb-3">
                    <?php 
                    $pid = $p['id_produk'];
                    $sizes_on_card = query("SELECT u.ukuran, s.stok FROM stok_produk s JOIN ukuran u ON s.id_ukuran = u.id_ukuran WHERE s.id_produk = $pid");
                    foreach($sizes_on_card as $soc): 
                        $is_low = ($soc['stok'] < 5);
                    ?>
                    <span class="text-[8px] font-bold <?php echo $is_low ? 'bg-rose-50 text-rose-500 border-rose-100' : 'bg-gray-50 text-gray-400 border-gray-100'; ?> border px-1.5 py-0.5 rounded shadow-sm">
                        <?php echo $soc['ukuran']; ?>: <span class="<?php echo $is_low ? 'text-rose-600' : 'text-[#3e2723]'; ?>"><?php echo $soc['stok']; ?></span>
                    </span>
                    <?php endforeach; ?>
                </div>

                <div class="flex items-center justify-between px-1">
                    <span class="font-bold text-xs text-[#3e2723]">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></span>
                    <button class="w-7 h-7 rounded-lg btn-primary flex items-center justify-center transition-colors shadow-lg">
                        <i class="ph ph-plus-bold text-[10px]"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="lg:w-[380px] flex-shrink-0">
        <div class="card-gradient rounded-3xl flex flex-col sticky top-[100px] bg-white border-t-8 border-[#3e2723] shadow-2xl" style="height: calc(100vh - 140px);">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold flex items-center gap-2 text-sm text-[#1a1a1a]">
                    <i class="ph ph-shopping-bag text-[#3e2723]"></i> Ringkasan
                </h3>
                <button onclick="clearCart()" class="text-[9px] text-rose-500 font-bold hover:underline uppercase">Reset</button>
            </div>
            
            <div id="cart_items" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll">
                <div class="flex flex-col items-center justify-center h-full text-center opacity-40">
                    <i class="ph ph-shopping-cart-simple text-4xl mb-3 text-[#3e2723]"></i>
                    <p class="text-[10px] text-gray-500 font-medium whitespace-nowrap">Keranjang kosong.</p>
                </div>
            </div>

            <form method="POST" id="checkout_form" class="p-5 bg-gray-50 rounded-b-3xl border-t border-gray-100 space-y-4">
                <input type="hidden" name="action" value="checkout">
                <input type="hidden" name="cart_data" id="cart_data_input">
                <input type="hidden" name="total_harga" id="total_harga_input">

                <!-- Discount Section -->
                <div class="bg-white/50 p-2 rounded-2xl border border-gray-100 shadow-sm space-y-1">
                    <label class="text-[9px] text-gray-400 uppercase font-black tracking-widest pl-1">Potongan Harga / Diskon</label>
                    <div class="flex gap-2">
                        <select id="discount_type" class="bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-[10px] font-bold text-[#3e2723] focus:outline-none" onchange="renderCart()">
                            <option value="nominal">Rp (Nominal)</option>
                            <option value="persen">% (Persen)</option>
                        </select>
                        <input type="number" id="discount_value" value="0" min="0" class="flex-1 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-[10px] font-bold text-[#3e2723] focus:outline-none" placeholder="0" oninput="renderCart()">
                    </div>
                </div>

                <div id="cash_section" class="bg-white/50 p-2 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[9px] text-gray-400 uppercase font-bold tracking-widest pl-1">Bayar (Tunai)</label>
                            <input type="number" id="cash_received" value="0" min="0" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-[10px] font-bold text-[#3e2723] focus:outline-none" oninput="renderCart()">
                        </div>
                        <div class="space-y-1 text-right">
                            <label class="text-[9px] text-gray-400 uppercase font-bold tracking-widest pr-1">Kembali</label>
                            <div id="cash_change_display" class="font-bold text-emerald-600 text-xs mt-0.5">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[9px] text-gray-400 uppercase font-bold tracking-widest pl-1">Metode Bayar</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="metode_pembayaran" value="offline" checked class="hidden peer">
                            <div class="py-2 text-center text-[10px] font-bold rounded-xl border border-gray-200 peer-checked:bg-[#3e2723] peer-checked:text-white peer-checked:border-[#3e2723] transition-all">OFFLINE</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="metode_pembayaran" value="online" class="hidden peer">
                            <div class="py-2 text-center text-[10px] font-bold rounded-xl border border-gray-200 peer-checked:bg-[#3e2723] peer-checked:text-white peer-checked:border-[#3e2723] transition-all">ONLINE</div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-between items-center text-base font-bold text-[#1a1a1a] pt-1">
                    <span>Total</span>
                    <span id="display_total" class="text-[#3e2723]">Rp 0</span>
                </div>
                <button type="submit" class="w-full btn-primary py-3.5 rounded-2xl font-bold flex items-center justify-center gap-2 uppercase tracking-widest text-[11px] shadow-xl">
                    BAYAR SEKARANG
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Size Selection Modal -->
<div id="size_modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[999] hidden items-center justify-center p-4">
    <div class="bg-white rounded-[32px] w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in border border-gray-100 max-h-[90vh] flex flex-col">
        <div class="relative h-40 flex-shrink-0">
            <img id="modal_product_img" src="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <button onclick="closeSizeModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/40 transition-all">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
            <div class="absolute bottom-4 left-6">
                <h4 id="modal_produk_nama" class="font-bold text-white text-lg uppercase tracking-tight">Pilih Ukuran</h4>
                <p id="modal_produk_harga" class="text-white/80 text-xs font-medium"></p>
            </div>
        </div>

        <div class="p-6 space-y-6 overflow-y-auto custom-scroll">
            <div>
                <label class="text-[10px] text-gray-400 uppercase font-bold tracking-[0.2em] block mb-3">Ukuran Tersedia</label>
                <div id="modal_sizes" class="grid grid-cols-4 gap-2"></div>
            </div>

            <div class="bg-gray-50 rounded-2xl p-4 flex items-center justify-between border border-gray-100">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Jumlah</span>
                    <input type="number" id="modal_qty_input" value="1" min="1" class="w-16 bg-transparent text-base font-bold text-[#3e2723] focus:outline-none" oninput="modalQty = parseInt(this.value) || 1">
                </div>
                <div class="flex items-center gap-1.5">
                    <button onclick="adjustModalQty(-1)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-[#3e2723] hover:border-[#3e2723] transition-all shadow-sm">
                        <i class="ph ph-minus text-[10px]"></i>
                    </button>
                    <button onclick="adjustModalQty(1)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-[#3e2723] hover:border-[#3e2723] transition-all shadow-sm">
                        <i class="ph ph-plus text-[10px]"></i>
                    </button>
                </div>
            </div>

            <button onclick="addToCart()" class="w-full bg-[#3e2723] text-white py-3.5 rounded-2xl font-bold uppercase tracking-[0.1em] text-[10px] shadow-xl hover:bg-[#5d4037] active:scale-95 transition-all">
                Masukan Keranjang
            </button>
        </div>
    </div>
</div>

<script>
let currentProduct = null;
let currentSize = null;
let modalQty = 1;
let cart = [];

const stockData = <?php 
    $allStocks = query("SELECT s.*, u.ukuran FROM stok_produk s JOIN ukuran u ON s.id_ukuran = u.id_ukuran");
    echo json_encode($allStocks); 
?>;

function openSizeModal(product) {
    currentProduct = product;
    currentSize = null;
    modalQty = 1;
    document.getElementById('modal_produk_nama').innerText = product.nama_produk;
    document.getElementById('modal_produk_harga').innerText = 'Rp ' + parseInt(product.harga).toLocaleString('id-ID');
    document.getElementById('modal_qty_input').value = modalQty;

    // Foto Preview
    let foto_url = "assets/images/produk/" + product.foto;
    if (!product.foto) foto_url = "https://ui-avatars.com/api/?name=" + encodeURIComponent(product.nama_produk) + "&background=3e2723&color=fff";
    document.getElementById('modal_product_img').src = foto_url;
    
    const availableSizes = stockData.filter(s => s.id_produk == product.id_produk);
    const sizeContainer = document.getElementById('modal_sizes');
    sizeContainer.innerHTML = '';
    
    if (availableSizes.length === 0) {
        sizeContainer.innerHTML = '<p class="col-span-full text-center text-[10px] text-rose-500 py-4 font-bold">STOK HABIS!</p>';
    } else {
        availableSizes.forEach(s => {
            const btn = document.createElement('button');
            const isLow = s.stok <= 5;
            btn.className = `py-3 rounded-2xl border border-gray-100 flex flex-col items-center justify-center transition-all ${isLow ? 'bg-rose-50/50' : 'bg-gray-50/50 hover:bg-white hover:shadow-sm'}`;
            btn.innerHTML = `
                <span class="text-[11px] font-black tracking-tighter">${s.ukuran}</span>
                <span class="stock-label text-[7px] font-bold uppercase tracking-widest ${isLow ? 'text-rose-500' : 'text-emerald-600'}" data-low="${isLow}">${s.stok} psg</span>
            `;
            btn.onclick = () => selectSize(btn, s);
            if (s.stok <= 0) {
                btn.disabled = true;
                btn.classList.add('opacity-30', 'grayscale', 'cursor-not-allowed', 'border-transparent');
            }
            sizeContainer.appendChild(btn);
        });
    }
    
    document.getElementById('size_modal').classList.remove('hidden');
    document.getElementById('size_modal').classList.add('flex');
}

function selectSize(el, sizeObj) {
    document.querySelectorAll('#modal_sizes button').forEach(b => {
        // Reset to default state
        b.classList.remove('bg-[#fdf8f5]', 'text-[#3e2723]', 'border-[#3e2723]', 'ring-2', 'ring-[#3e2723]/10');
        b.classList.add('bg-gray-50/50', 'border-gray-100');
        
        const stockSpan = b.querySelector('.stock-label');
        if (stockSpan) {
            if (stockSpan.dataset.low === 'true') {
                stockSpan.classList.add('text-rose-500');
            } else {
                stockSpan.classList.add('text-emerald-600');
            }
            stockSpan.classList.remove('text-[#3e2723]');
        }
    });

    // Terapkan style coklat-cream yang elegan
    el.classList.add('bg-[#fdf8f5]', 'text-[#3e2723]', 'border-[#3e2723]', 'ring-2', 'ring-[#3e2723]/10');
    el.classList.remove('bg-gray-50/50', 'border-gray-100');
    
    // Pastikan teks stok (psg) jadi coklat pekat sesuai permintaan
    const selectedStockSpan = el.querySelector('.stock-label');
    if (selectedStockSpan) {
        selectedStockSpan.classList.remove('text-rose-500', 'text-emerald-600');
        selectedStockSpan.classList.add('text-[#3e2723]');
    }

    currentSize = sizeObj;
    modalQty = 1;
    document.getElementById('modal_qty_input').value = modalQty;
}

function adjustModalQty(delta) {
    if (modalQty + delta >= 1) {
        if (currentSize && modalQty + delta > currentSize.stok) {
            alert('Stok tidak cukup!');
            return;
        }
        modalQty += delta;
        document.getElementById('modal_qty_input').value = modalQty;
    }
}

function closeSizeModal() {
    document.getElementById('size_modal').classList.add('hidden');
    document.getElementById('size_modal').classList.remove('flex');
}

function addToCart() {
    if (!currentSize) {
        alert('Pilih ukuran terlebih dahulu!');
        return;
    }
    const cartId = currentProduct.id_produk + '-' + currentSize.id_ukuran;
    const existing = cart.find(item => item.cartId === cartId);
    if (existing) {
        if (existing.qty + modalQty > currentSize.stok) {
            alert('Stok tidak mencukupi!');
            return;
        }
        existing.qty += modalQty;
    } else {
        cart.push({
            cartId: cartId,
            id_produk: currentProduct.id_produk,
            nama_produk: currentProduct.nama_produk,
            id_ukuran: currentSize.id_ukuran,
            ukuran: currentSize.ukuran,
            qty: modalQty,
            price: currentProduct.harga
        });
    }
    renderCart();
    closeSizeModal();
}

function renderCart() {
    const container = document.getElementById('cart_items');
    if (cart.length === 0) {
        container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-center opacity-40">
            <i class="ph ph-shopping-cart-simple text-4xl mb-3 text-[#3e2723]"></i>
            <p class="text-[10px] text-gray-500 font-medium whitespace-nowrap">Keranjang kosong.</p>
        </div>`;
        document.getElementById('display_total').innerText = 'Rp 0';
        return;
    }
    let html = '';
    let total = 0;
    cart.forEach((item, index) => {
        const sub = item.qty * item.price;
        total += sub;
        html += `
            <div class="p-3 bg-white border border-gray-100 rounded-2xl flex items-center justify-between group shadow-sm">
                <div class="flex-1">
                    <p class="text-[11px] font-bold text-[#1a1a1a] line-clamp-1 uppercase">${item.nama_produk}</p>
                    <p class="text-[9px] text-gray-400 font-medium">UK: ${item.ukuran} | @${parseInt(item.price).toLocaleString('id-ID')}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-gray-50 rounded-lg p-1">
                        <button onclick="updateQty(${index}, -1)" class="w-4 h-4 flex items-center justify-center text-[#3e2723] hover:bg-gray-200 rounded"><i class="ph ph-minus text-[8px]"></i></button>
                        <input type="number" value="${item.qty}" min="1" class="text-[10px] font-bold w-10 text-center bg-transparent focus:outline-none" onchange="updateQty(${index}, parseInt(this.value) - ${item.qty})">
                        <button onclick="updateQty(${index}, 1)" class="w-4 h-4 flex items-center justify-center text-[#3e2723] hover:bg-gray-200 rounded"><i class="ph ph-plus text-[8px]"></i></button>
                    </div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;

    // Hitung Diskon
    const discType = document.getElementById('discount_type').value;
    const discVal = parseFloat(document.getElementById('discount_value').value) || 0;
    let potongan = 0;

    if (discType === 'persen') {
        potongan = total * (discVal / 100);
    } else {
        potongan = discVal;
    }

    const finalTotal = Math.max(0, total - potongan);

    document.getElementById('display_total').innerText = 'Rp ' + finalTotal.toLocaleString('id-ID');
    document.getElementById('total_harga_input').value = finalTotal;
    document.getElementById('cart_data_input').value = JSON.stringify(cart);

    // Hitung Kembalian
    const cashReceived = parseFloat(document.getElementById('cash_received').value) || 0;
    const change = Math.max(0, cashReceived - finalTotal);
    document.getElementById('cash_change_display').innerText = 'Rp ' + change.toLocaleString('id-ID');
}

// Show/Hide Cash Input based on Payment Method
document.querySelectorAll('input[name="metode_pembayaran"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const cashSection = document.getElementById('cash_section');
        if (this.value === 'online') {
            cashSection.classList.add('hidden');
        } else {
            cashSection.classList.remove('hidden');
        }
    });
});

function updateQty(index, delta) {
    const item = cart[index];
    const size = stockData.find(s => s.id_produk == item.id_produk && s.id_ukuran == item.id_ukuran);
    if (item.qty + delta >= 1) {
        if (delta > 0 && item.qty + delta > size.stok) { alert('Stok tidak cukup!'); return; }
        item.qty += delta;
        renderCart();
    } else {
        cart.splice(index, 1);
        renderCart();
    }
}

function clearCart() {
    if (confirm('Bersihkan keranjang?')) { cart = []; renderCart(); }
}

document.getElementById('search_produk').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.grid > .card-gradient');
    cards.forEach(card => {
        const name = card.querySelector('h4').innerText.toLowerCase();
        card.style.display = name.includes(term) ? 'block' : 'none';
    });
});
</script>

<style>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
