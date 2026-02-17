<?php
// views/pembelian.php
$categories = query("SELECT * FROM kategori");
$suppliers = query("SELECT * FROM supplier ORDER BY nama_supplier ASC");
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

// Handle Save Pembelian (Batch)
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_pembelian_batch') {
    $cart = json_decode($_POST['cart_data'], true);
    $id_supplier = (int)$_POST['id_supplier'];
    $biaya_tambahan = (float)$_POST['biaya_tambahan'];
    $total_all = (float)$_POST['total_pembelian'];
    $keterangan = escape($_POST['keterangan']);

    // Bisa simpan kalau ada barang OR ada biaya tambahan (bahan baku)
    if (!empty($cart) || $biaya_tambahan > 0) {
        // 1. Simpan ke tabel pembelian (Termasuk kolom biaya_tambahan)
        $query_pembelian = "INSERT INTO pembelian (id_supplier, total_pembelian, biaya_tambahan, keterangan) 
                            VALUES ($id_supplier, $total_all, $biaya_tambahan, '$keterangan')";
        
        if (query($query_pembelian)) {
            $id_pembelian = lastInsertId();
            
            // Simpan detail produk jika ada
            if (!empty($cart)) {
                foreach ($cart as $item) {
                    $id_p = (int)$item['id_produk'];
                    $id_u = (int)$item['id_ukuran'];
                    $qty = (int)$item['qty'];
                    $harga_beli = (float)$item['harga_beli'];
                    $sub = $qty * $harga_beli;

                    // Cek apakah data stok_produk sudah ada, jika tidak buat dulu
                    $check_stok = query("SELECT id_stok FROM stok_produk WHERE id_produk = $id_p AND id_ukuran = $id_u");
                    if (empty($check_stok)) {
                        query("INSERT INTO stok_produk (id_produk, id_ukuran, stok) VALUES ($id_p, $id_u, 0)");
                    }

                    // Simpan ke detail_pembelian
                    query("INSERT INTO detail_pembelian (id_pembelian, id_produk, id_ukuran, jumlah, harga_beli, subtotal) 
                           VALUES ($id_pembelian, $id_p, $id_u, $qty, $harga_beli, $sub)");
                }
            }
            $message = "<script>alert('Pencatatan Berhasil!'); window.location.href='?page=pembelian';</script>";
        }
    }
}
?>

<?php echo $message; ?>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- Left: Product Selection -->
    <div class="flex-1 w-full space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full max-w-md">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="search_produk" placeholder="Cari Produk untuk Restok..." class="w-full bg-white border border-gray-200 rounded-2xl py-3 pl-12 pr-4 text-sm focus:outline-none focus:border-[#3e2723] transition-all text-[#1a1a1a] shadow-sm">
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 no-scrollbar">
                <a href="?page=pembelian" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?php echo !$selected_kat ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
                    Semua
                </a>
                <?php foreach($categories as $kat): ?>
                <a href="?page=pembelian&kat=<?php echo $kat['id_kategori']; ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all <?php echo $selected_kat == $kat['id_kategori'] ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
                    <?php echo $kat['nama_kategori']; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php if (empty($products)): ?>
                <div class="col-span-full card-gradient p-10 text-center text-gray-400">
                    <i class="ph ph-package text-4xl mb-2 text-[#3e2723]"></i>
                    <p>Produk tidak ditemukan.</p>
                </div>
            <?php else: foreach($products as $p): 
                $foto_url = "assets/images/produk/" . $p['foto'];
                if (!file_exists($foto_url) || empty($p['foto'])) $foto_url = "https://ui-avatars.com/api/?name=" . urlencode($p['nama_produk']) . "&background=3e2723&color=fff";
            ?>
            <div onclick="openRestockModal(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="card-gradient p-3 rounded-3xl group cursor-pointer hover:border-[#3e2723]/50 transition-all bg-white relative">
                <div class="aspect-square rounded-2xl bg-gray-50 relative overflow-hidden mb-3">
                    <img src="<?php echo $foto_url; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute top-2 right-2 px-2 py-1 bg-amber-500/90 backdrop-blur-sm text-white text-[9px] font-bold rounded-lg shadow-md">
                        Stok Sekarang: <?php echo $p['total_stok'] ?? 0; ?>
                    </div>
                </div>
                <h4 class="font-bold text-[11px] mb-1 line-clamp-1 text-[#1a1a1a] uppercase px-1"><?php echo $p['nama_produk']; ?></h4>
                <div class="flex items-center justify-between px-1 mt-3">
                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">Klik untuk Restok</span>
                    <button class="w-7 h-7 rounded-lg bg-[#3e2723] text-white flex items-center justify-center transition-colors shadow-lg">
                        <i class="ph ph-plus-bold text-[10px]"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Right: Purchase List & Submission -->
    <div class="lg:w-[380px] flex-shrink-0">
        <div class="card-gradient rounded-3xl flex flex-col sticky top-[100px] bg-white border-t-8 border-[#3e2723] shadow-2xl" style="height: calc(100vh - 140px);">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold flex items-center gap-2 text-sm text-[#1a1a1a]">
                    <i class="ph ph-truck text-[#3e2723]"></i> Daftar Restok
                </h3>
                <button onclick="clearPurchaseCart()" class="text-[9px] text-rose-500 font-bold hover:underline uppercase">Reset</button>
            </div>
            
            <div id="purchase_items" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll">
                <div class="flex flex-col items-center justify-center h-full text-center opacity-40">
                    <i class="ph ph-tray text-4xl mb-3 text-[#3e2723]"></i>
                    <p class="text-[10px] text-gray-500 font-medium">Belum ada barang dipilih.</p>
                </div>
            </div>

            <form method="POST" id="pembelian_form" class="p-5 bg-gray-50 rounded-b-3xl border-t border-gray-100 space-y-4">
                <input type="hidden" name="action" value="save_pembelian_batch">
                <input type="hidden" name="cart_data" id="cart_data_input">
                <input type="hidden" name="total_pembelian" id="total_pembelian_input">

                <!-- Supplier Selection -->
                <div class="space-y-2">
                    <label class="text-[9px] text-gray-400 uppercase font-bold tracking-widest pl-1">Supplier</label>
                    <select name="id_supplier" required class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-[10px] font-bold text-[#3e2723] focus:outline-none focus:border-[#3e2723] shadow-sm">
                        <option value="">-- PILIH SUPPLIER --</option>
                        <?php foreach($suppliers as $s): ?>
                        <option value="<?php echo $s['id_supplier']; ?>"><?php echo $s['nama_supplier']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- NEW: Extra Cost (Bahan Baku etc) -->
                <div class="space-y-2">
                    <label class="text-[9px] text-gray-400 uppercase font-bold tracking-widest pl-1">Biaya Tambahan (Bahan Baku / Lainnya)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400">Rp</span>
                        <input type="number" id="extra_cost" name="biaya_tambahan" value="0" min="0" class="w-full bg-white border border-gray-200 rounded-xl py-2 pl-8 pr-3 text-[10px] font-bold text-[#3e2723] focus:outline-none focus:border-[#3e2723] shadow-sm" oninput="renderPurchaseCart()">
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="space-y-2">
                    <label class="text-[9px] text-gray-400 uppercase font-bold tracking-widest pl-1">Keterangan / Catatan</label>
                    <textarea name="keterangan" rows="2" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-[10px] font-bold text-[#3e2723] focus:outline-none focus:border-[#3e2723] shadow-sm" placeholder="Contoh: Belanja bahan baku kulit dan restok model X"></textarea>
                </div>

                <div class="flex justify-between items-center text-base font-bold text-[#1a1a1a] pt-1 border-t border-gray-100 mt-2">
                    <span class="text-xs uppercase tracking-widest text-gray-400">Total Biaya</span>
                    <span id="display_total" class="text-[#3e2723]">Rp 0</span>
                </div>

                <button type="submit" class="w-full btn-primary py-3.5 rounded-2xl font-bold flex items-center justify-center gap-2 uppercase tracking-widest text-[11px] shadow-xl">
                    SIMPAN PEMBELIAN STOK
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div id="restock_modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-[32px] w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in border border-gray-100">
        <div class="relative h-48 bg-gray-50">
            <img id="modal_product_img" src="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <button onclick="closeRestockModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/40 transition-all">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
            <div class="absolute bottom-4 left-6">
                <h4 id="modal_produk_nama" class="font-bold text-white text-lg uppercase tracking-tight">Restok Produk</h4>
                <p id="modal_produk_total_stok" class="text-white/80 text-xs font-medium"></p>
            </div>
        </div>

        <div class="p-8 space-y-6">
            <!-- Part 1: Grid of All Sizes with Qty Inputs -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em]">Input Stok Masuk</label>
                    <span id="modal_total_input" class="text-[9px] font-bold text-[#3e2723] bg-gray-50 px-2 py-1 rounded-md">Total: 0 psg</span>
                </div>
                <div id="modal_size_grid" class="grid grid-cols-3 gap-2 max-h-60 overflow-y-auto p-2 custom-scroll bg-gray-50/50 rounded-2xl border border-gray-100">
                    <!-- JS will fill this -->
                </div>
            </div>

            <!-- Part 2: Shared Info (Optional Price) -->
            <div class="space-y-4 pt-2 border-t border-gray-100">
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-black tracking-widest pl-1">Harga Beli Satuan <span class="text-[8px] text-gray-300 italic font-normal">(Opsional)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400">Rp</span>
                        <input type="number" id="modal_harga_beli" placeholder="0" class="w-full bg-white border border-gray-200 rounded-xl py-3 pl-8 pr-3 text-xs font-bold text-[#3e2723] focus:outline-none focus:border-[#3e2723] shadow-sm">
                    </div>
                </div>
            </div>

            <button onclick="addToPurchaseList()" class="w-full bg-[#3e2723] text-white py-4 rounded-2xl font-bold uppercase tracking-[0.1em] text-[11px] shadow-xl shadow-[#3e2723]/20 hover:bg-[#5d4037] active:scale-95 transition-all">
                Tambahkan ke Daftar
            </button>
        </div>
    </div>
</div>

<script>
let currentProduct = null;
let selectedSizes = [];
let modalQty = 1;
let purchaseCart = [];

const allSizes = <?php 
    $all_sizes = query("SELECT * FROM ukuran ORDER BY ukuran ASC");
    echo json_encode($all_sizes); 
?>;

function openRestockModal(product) {
    currentProduct = product;
    document.getElementById('modal_produk_nama').innerText = product.nama_produk;
    document.getElementById('modal_produk_total_stok').innerText = 'Stok: ' + (product.total_stok || 0) + ' psg';
    document.getElementById('modal_harga_beli').value = '';

    // Foto Preview
    let foto_url = "assets/images/produk/" + product.foto;
    if (!product.foto) foto_url = "https://ui-avatars.com/api/?name=" + encodeURIComponent(product.nama_produk) + "&background=3e2723&color=fff";
    document.getElementById('modal_product_img').src = foto_url;
    
    // Render Size Grid
    const grid = document.getElementById('modal_size_grid');
    grid.innerHTML = '';
    
    allSizes.forEach(s => {
        const cartId = product.id_produk + '-' + s.id_ukuran;
        const inCart = purchaseCart.find(item => item.cartId === cartId);
        const qtyVal = inCart ? inCart.qty : 0;
        const isActive = qtyVal > 0;

        const card = document.createElement('div');
        card.id = 'card_' + s.id_ukuran;
        card.className = `cursor-pointer p-2 rounded-xl border transition-all flex flex-col items-center justify-center gap-1.5 ${isActive ? 'bg-[#fdf8f5] border-[#3e2723] ring-1 ring-[#3e2723]' : 'bg-white border-gray-100 hover:border-gray-200'}`;
        card.innerHTML = `
            <span class="text-[10px] font-black ${isActive ? 'text-[#3e2723]' : 'text-gray-400'}">UK ${s.ukuran}</span>
            <div id="qty_container_${s.id_ukuran}" class="${isActive ? '' : 'hidden'} w-full animate-fade-in px-1">
                <input type="number" id="qty_${s.id_ukuran}" value="${qtyVal > 0 ? qtyVal : ''}" placeholder="0" 
                    onclick="event.stopPropagation()"
                    class="w-full bg-white border border-gray-200 rounded-lg py-1 px-1 text-[10px] font-black text-[#3e2723] text-center focus:outline-none focus:border-[#3e2723] shadow-inner [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
            </div>
        `;

        card.onclick = () => {
            const container = document.getElementById('qty_container_' + s.id_ukuran);
            const input = document.getElementById('qty_' + s.id_ukuran);
            const span = card.querySelector('span');

            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                card.classList.add('bg-[#fdf8f5]', 'border-[#3e2723]', 'ring-1', 'ring-[#3e2723]');
                card.classList.remove('bg-white', 'border-gray-100', 'hover:border-gray-200');
                span.classList.add('text-[#3e2723]');
                span.classList.remove('text-gray-400');
                input.focus();
            } else {
                if (!input.value || input.value == 0) {
                    container.classList.add('hidden');
                    card.classList.remove('bg-[#fdf8f5]', 'border-[#3e2723]', 'ring-1', 'ring-[#3e2723]');
                    card.classList.add('bg-white', 'border-gray-100', 'hover:border-gray-200');
                    span.classList.remove('text-[#3e2723]');
                    span.classList.add('text-gray-400');
                }
            }
        };

        // Re-attach input event because innerHTML replacement lost it
        card.querySelector('input').oninput = updateModalTotal;

        grid.appendChild(card);
    });
    
    updateModalTotal();
    document.getElementById('restock_modal').classList.remove('hidden');
    document.getElementById('restock_modal').classList.add('flex');
}

function updateModalTotal() {
    let total = 0;
    allSizes.forEach(s => {
        const val = parseInt(document.getElementById('qty_' + s.id_ukuran).value) || 0;
        total += val;
    });
    document.getElementById('modal_total_input').innerText = `Total: ${total} psg`;
}

function selectRestockSize(el, sizeObj) {
    const index = selectedSizes.findIndex(s => s.id_ukuran === sizeObj.id_ukuran);
    
    if (index > -1) {
        // Toggle OFF
        selectedSizes.splice(index, 1);
        el.classList.remove('bg-[#fdf8f5]', 'text-[#3e2723]', 'border-[#3e2723]', 'ring-2', 'ring-[#3e2723]/10');
        el.classList.add('bg-gray-50/50', 'border-gray-100');
    } else {
        // Toggle ON
        selectedSizes.push(sizeObj);
        el.classList.add('bg-[#fdf8f5]', 'text-[#3e2723]', 'border-[#3e2723]', 'ring-2', 'ring-[#3e2723]/10');
        el.classList.remove('bg-gray-50/50', 'border-gray-100');
    }
}

// function adjustRestockQty removed

function closeRestockModal() {
    document.getElementById('restock_modal').classList.add('hidden');
    document.getElementById('restock_modal').classList.remove('flex');
}

function addToPurchaseList() {
    const hargaBeli = parseFloat(document.getElementById('modal_harga_beli').value) || 0;
    let addedCount = 0;

    allSizes.forEach(s => {
        const qty = parseInt(document.getElementById('qty_' + s.id_ukuran).value) || 0;
        if (qty > 0) {
            const cartId = currentProduct.id_produk + '-' + s.id_ukuran;
            const existingIndex = purchaseCart.findIndex(item => item.cartId === cartId);
            
            if (existingIndex > -1) {
                purchaseCart[existingIndex].qty = qty; // Overwrite or add? Usually in modal grid we want to set the final qty
                if (hargaBeli > 0) purchaseCart[existingIndex].harga_beli = hargaBeli;
            } else {
                purchaseCart.push({
                    cartId: cartId,
                    id_produk: currentProduct.id_produk,
                    nama_produk: currentProduct.nama_produk,
                    id_ukuran: s.id_ukuran,
                    ukuran: s.ukuran,
                    qty: qty,
                    harga_beli: hargaBeli
                });
            }
            addedCount++;
        } else {
            // Remove if qty set to 0
            const cartId = currentProduct.id_produk + '-' + s.id_ukuran;
            const existingIndex = purchaseCart.findIndex(item => item.cartId === cartId);
            if (existingIndex > -1) purchaseCart.splice(existingIndex, 1);
        }
    });
    
    if (addedCount === 0 && purchaseCart.length > 0) {
        // Just render if we possibly cleared something
    } else if (addedCount === 0) {
        alert('Masukkan jumlah untuk minimal satu ukuran!');
        return;
    }
    
    renderPurchaseCart();
    closeRestockModal();
}

function renderPurchaseCart() {
    const container = document.getElementById('purchase_items');
    const extraCost = parseFloat(document.getElementById('extra_cost').value) || 0;

    if (purchaseCart.length === 0 && extraCost === 0) {
        container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-center opacity-40">
            <i class="ph ph-tray text-4xl mb-3 text-[#3e2723]"></i>
            <p class="text-[10px] text-gray-500 font-medium whitespace-nowrap">Belum ada barang dipilih.</p>
        </div>`;
        document.getElementById('display_total').innerText = 'Rp 0';
        return;
    }

    let html = '';
    let itemsTotal = 0;
    
    // Render Items
    purchaseCart.forEach((item, index) => {
        const sub = item.qty * item.harga_beli;
        itemsTotal += sub;
        const priceDisplay = item.harga_beli > 0 ? `@Rp ${parseInt(item.harga_beli).toLocaleString('id-ID')}` : '<span class="text-gray-300 italic">Tanpa Harga Satuan</span>';
        
        html += `
            <div class="p-3 bg-white border border-gray-100 rounded-2xl flex items-center justify-between group shadow-sm">
                <div class="flex-1">
                    <p class="text-[11px] font-black text-[#1a1a1a] uppercase line-clamp-1">${item.nama_produk}</p>
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">UK ${item.ukuran} | ${priceDisplay}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-gray-50 rounded-lg p-1">
                        <button onclick="updatePurchaseQty(${index}, -1)" class="w-5 h-5 flex items-center justify-center text-[#3e2723] hover:bg-gray-200 rounded"><i class="ph ph-minus text-[8px]"></i></button>
                        <span class="text-[10px] font-bold w-6 text-center">${item.qty}</span>
                        <button onclick="updatePurchaseQty(${index}, 1)" class="w-5 h-5 flex items-center justify-center text-[#3e2723] hover:bg-gray-200 rounded"><i class="ph ph-plus text-[8px]"></i></button>
                    </div>
                    <button onclick="removePurchaseItem(${index})" class="text-gray-300 hover:text-rose-500 transition-colors p-1">
                        <i class="ph-bold ph-trash text-xs"></i>
                    </button>
                </div>
            </div>
        `;
    });

    // Render Extra Cost as a special item in list if > 0
    if (extraCost > 0) {
        html += `
            <div class="p-3 bg-rose-50/50 border border-rose-100 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex-1">
                    <p class="text-[10px] font-black text-rose-600 uppercase">Biaya Bahan Baku / Lainnya</p>
                    <p class="text-[9px] text-rose-400 font-bold uppercase">Biaya Tambahan Operasional</p>
                </div>
                <p class="text-[10px] font-black text-rose-600">Rp ${extraCost.toLocaleString('id-ID')}</p>
            </div>
        `;
    }
    
    container.innerHTML = html;
    const finalTotal = itemsTotal + extraCost;
    document.getElementById('display_total').innerText = 'Rp ' + finalTotal.toLocaleString('id-ID');
    document.getElementById('total_pembelian_input').value = finalTotal;
    document.getElementById('cart_data_input').value = JSON.stringify(purchaseCart);
}

function updatePurchaseQty(index, delta) {
    if (purchaseCart[index].qty + delta >= 1) {
        purchaseCart[index].qty += delta;
    }
    renderPurchaseCart();
}

function removePurchaseItem(index) {
    purchaseCart.splice(index, 1);
    renderPurchaseCart();
}

function clearPurchaseCart() {
    if (confirm('Bersihkan daftar belanja?')) { 
        purchaseCart = []; 
        document.getElementById('extra_cost').value = 0;
        renderPurchaseCart(); 
    }
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
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-thumb { background: #3e272320; border-radius: 10px; }
</style>
