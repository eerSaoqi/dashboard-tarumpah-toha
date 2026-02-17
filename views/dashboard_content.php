<?php
// views/dashboard_content.php

$range = isset($_GET['range']) ? $_GET['range'] : 'all';
$where_trx = "WHERE status = 'selesai'";
$where_pending = "WHERE status = 'diproses'";
$where_recent = "";

switch ($range) {
    case 'today':
        $date_cond = " AND DATE(tanggal) = CURDATE()";
        $where_trx .= $date_cond;
        $where_pending .= $date_cond;
        $where_recent = " WHERE DATE(tanggal) = CURDATE()";
        break;
    case 'week':
        $date_cond = " AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $where_trx .= $date_cond;
        $where_pending .= $date_cond;
        $where_recent = " WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $date_cond = " AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
        $where_trx .= $date_cond;
        $where_pending .= $date_cond;
        $where_recent = " WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
        break;
    case 'year':
        $date_cond = " AND YEAR(tanggal) = YEAR(CURDATE())";
        $where_trx .= $date_cond;
        $where_pending .= $date_cond;
        $where_recent = " WHERE YEAR(tanggal) = YEAR(CURDATE())";
        break;
}

// Sesuai Flowchart: Laporan hanya menghitung yang berstatus 'selesai'
$total_pendapatan = sumColumn('transaksi', 'total_harga', $where_trx);
$total_transaksi_selesai = countRows('transaksi', $where_trx);
$total_stok = sumColumn('stok_produk s JOIN produk_tarumpah p ON s.id_produk = p.id_produk', 's.stok', "WHERE p.status = 'aktif'");
$transaksi_pending = countRows('transaksi', $where_pending);
$stok_krisis = countRows('stok_produk s JOIN produk_tarumpah p ON s.id_produk = p.id_produk', "WHERE s.stok <= 5 AND p.status = 'aktif'");

$recent_trxs = query("SELECT * FROM transaksi $where_recent ORDER BY tanggal DESC LIMIT 5");
?>

<!-- Time Filter Tabs (Style like Category) -->
<div class="mb-8 flex items-center justify-between">
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 md:pb-0">
        <a href="?page=dashboard&range=all" class="px-6 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $range == 'all' ? 'bg-[#3e2723] text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Semua Waktu
        </a>
        <a href="?page=dashboard&range=today" class="px-6 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $range == 'today' ? 'bg-[#3e2723] text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Hari Ini
        </a>
        <a href="?page=dashboard&range=week" class="px-6 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $range == 'week' ? 'bg-[#3e2723] text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100' ?>">
            7 Hari Terakhir
        </a>
        <a href="?page=dashboard&range=month" class="px-6 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $range == 'month' ? 'bg-[#3e2723] text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Bulan Ini
        </a>
        <a href="?page=dashboard&range=year" class="px-6 py-2.5 rounded-2xl text-xs font-bold transition-all <?php echo $range == 'year' ? 'bg-[#3e2723] text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Tahun Ini
        </a>
    </div>
    
    <p class="hidden md:block text-[10px] font-bold text-gray-400 uppercase tracking-widest">
        Filter Aktif: <span class="text-[#3e2723]"><?php echo strtoupper($range); ?></span>
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
    <!-- Stat Cards -->
    <div class="card-gradient p-6 rounded-3xl relative overflow-hidden group border-b-4 border-emerald-500">
        <div class="relative z-10">
            <p class="text-[10px] text-gray-500 uppercase font-black tracking-[0.2em] mb-1">Total Pendapatan</p>
            <h3 class="text-xl font-black mb-4 text-[#1a1a1a]">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h3>
            <div class="flex items-center gap-2 text-emerald-600 text-xs font-bold">
                <i class="ph-bold ph-trend-up"></i>
                <span>Status: Selesai</span>
            </div>
        </div>
        <i class="ph-bold ph-money absolute -right-4 -bottom-4 text-7xl text-emerald-500/10 group-hover:rotate-12 transition-transform duration-700"></i>
    </div>

    <div class="card-gradient p-6 rounded-3xl relative overflow-hidden group border-b-4 border-indigo-400">
        <div class="relative z-10">
            <p class="text-[10px] text-gray-500 uppercase font-black tracking-[0.2em] mb-1">Jumlah Transaksi</p>
            <h3 class="text-xl font-black mb-4 text-[#1a1a1a]"><?php echo $total_transaksi_selesai; ?> Transaksi</h3>
            <div class="flex items-center gap-2 text-indigo-500 text-xs font-bold">
                <i class="ph-bold ph-receipt"></i>
                <span>Selesai</span>
            </div>
        </div>
        <i class="ph-bold ph-receipt absolute -right-4 -bottom-4 text-7xl text-indigo-500/10 group-hover:rotate-12 transition-transform duration-700"></i>
    </div>

    <div class="card-gradient p-6 rounded-3xl relative overflow-hidden group border-b-4 border-[#3e2723]">
        <div class="relative z-10">
            <p class="text-[10px] text-gray-500 uppercase font-black tracking-[0.2em] mb-1">Stok Tersedia</p>
            <h3 class="text-xl font-black mb-4 text-[#1a1a1a]"><?php echo number_format($total_stok, 0, ',', '.'); ?> Item</h3>
            <div class="flex items-center gap-2 text-[#3e2723] text-xs font-bold">
                <i class="ph-bold ph-package"></i>
                <span>Gudang</span>
            </div>
        </div>
        <i class="ph-bold ph-sneaker absolute -right-4 -bottom-4 text-7xl text-[#3e2723]/10 group-hover:-rotate-12 transition-transform duration-700"></i>
    </div>

    <div class="card-gradient p-6 rounded-3xl relative overflow-hidden group border-b-4 border-amber-500">
        <div class="relative z-10">
            <p class="text-[10px] text-gray-500 uppercase font-black tracking-[0.2em] mb-1">Transaksi Diproses</p>
            <h3 class="text-xl font-black mb-4 text-amber-600"><?php echo $transaksi_pending; ?> Trx</h3>
            <div class="flex items-center gap-2 text-amber-600 text-xs font-bold">
                <i class="ph-bold ph-clock"></i>
                <span>Pending (Online)</span>
            </div>
        </div>
        <i class="ph-bold ph-clock-counter-clockwise absolute -right-4 -bottom-4 text-7xl text-amber-500/10 group-hover:rotate-12 transition-transform duration-700"></i>
    </div>

    <div class="card-gradient p-6 rounded-3xl relative overflow-hidden group border-b-4 border-rose-500">
        <div class="relative z-10">
            <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mb-1">Stok Krisis</p>
            <h3 class="text-xl font-black mb-4 text-rose-600"><?php echo $stok_krisis; ?> Varian</h3>
            <div class="flex items-center gap-2 text-rose-500 text-xs font-bold">
                <i class="ph-bold ph-warning"></i>
                <span>Perlu Re-order</span>
            </div>
        </div>
        <i class="ph-bold ph-warning absolute -right-4 -bottom-4 text-7xl text-rose-500/10 group-hover:-rotate-12 transition-transform duration-700"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 card-gradient rounded-3xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-[#1a1a1a]">Transaksi Terakhir</h3>
            <a href="?page=transaksi" class="text-xs text-[#3e2723] hover:underline font-bold">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] text-gray-400 uppercase tracking-widest border-b border-gray-100">
                        <th class="pb-4 font-bold">ID TRX</th>
                        <th class="pb-4 font-bold">Waktu</th>
                        <th class="pb-4 font-bold">Status</th>
                        <th class="pb-4 font-bold text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($recent_trxs)): ?>
                        <tr><td colspan="4" class="py-10 text-center text-gray-400 font-medium">Belum ada riwayat transaksi.</td></tr>
                    <?php else: foreach($recent_trxs as $trx): 
                        $is_pending = ($trx['status'] == 'diproses');
                    ?>
                    <tr class="border-b border-gray-50 group hover:bg-gray-50 transition-colors">
                        <td class="py-4 font-mono text-[#3e2723] font-bold">#TRX-<?php echo $trx['id_transaksi']; ?></td>
                        <td class="py-4 text-gray-600"><?php echo date('d/m/Y H:i', strtotime($trx['tanggal'])); ?></td>
                        <td class="py-4">
                            <?php if($trx['status'] == 'diproses'): ?>
                                <span class="px-2 py-1 bg-amber-50 text-amber-600 rounded text-[10px] font-bold uppercase">Diproses</span>
                            <?php elseif($trx['status'] == 'dibatalkan'): ?>
                                <span class="px-2 py-1 bg-rose-50 text-rose-600 rounded text-[10px] font-bold uppercase">Batal</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded text-[10px] font-bold uppercase">Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 text-right font-bold text-[#1a1a1a]">Rp <?php echo number_format($trx['total_harga'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card-gradient rounded-3xl p-6">
            <h3 class="text-lg font-bold mb-6 text-[#1a1a1a]">Navigasi Cepat</h3>
            <div class="grid grid-cols-2 gap-3 font-bold">
                <a href="?page=kasir" class="p-4 rounded-2xl bg-[#3e2723] text-white hover:bg-[#5d4037] transition-all text-center shadow-md">
                    <i class="ph ph-shopping-cart text-2xl mb-2 mx-auto"></i>
                    <p class="text-xs">Buka Kasir</p>
                </a>
                <a href="?page=pembelian" class="p-4 rounded-2xl bg-white border border-gray-200 text-[#3e2723] hover:border-[#3e2723] transition-all text-center shadow-sm">
                    <i class="ph ph-plus-circle text-2xl mb-2 mx-auto"></i>
                    <p class="text-xs">Input Stok</p>
                </a>
            </div>
        </div>
        
        <!-- Stock Alert List -->
        <?php if($stok_krisis > 0): ?>
        <div class="card-gradient rounded-3xl p-6 border-l-4 border-rose-500 bg-rose-50/10">
            <h3 class="text-sm font-bold mb-4 text-rose-600 uppercase tracking-widest">Stok Menipis!</h3>
            <div class="space-y-3">
                <?php 
                $krisis_items = query("SELECT p.nama_produk, u.ukuran, s.stok 
                                      FROM stok_produk s 
                                      JOIN produk_tarumpah p ON s.id_produk = p.id_produk 
                                      JOIN ukuran u ON s.id_ukuran = u.id_ukuran 
                                      WHERE s.stok <= 5 AND p.status = 'aktif' LIMIT 3");
                foreach($krisis_items as $ki): 
                ?>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600"><?php echo $ki['nama_produk']; ?> (<?php echo $ki['ukuran']; ?>)</span>
                    <span class="font-bold text-rose-600"><?php echo $ki['stok']; ?> pasang</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
