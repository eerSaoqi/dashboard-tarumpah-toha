<?php
// views/transaksi.php
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Handle Selesaikan Transaksi Online
if (isset($_POST['action']) && $_POST['action'] == 'selesaikan_trx') {
    $id_trx = (int)$_POST['id_transaksi'];
    query("UPDATE transaksi SET status = 'selesai' WHERE id_transaksi = $id_trx");
    echo "<script>alert('Transaksi Berhasil Diselesaikan!'); window.location.href='?page=transaksi';</script>";
}

// Handle Batalkan Transaksi Online (Kembalikan Stok)
if (isset($_POST['action']) && $_POST['action'] == 'batalkan_trx') {
    $id_trx = (int)$_POST['id_transaksi'];
    
    // 1. Ambil detail barang untuk dikembalikan stoknya
    $items = query("SELECT id_produk, id_ukuran, jumlah FROM detail_transaksi WHERE id_transaksi = $id_trx");
    
    // 2. Loop dan kembalikan stok
    foreach ($items as $item) {
        $id_p = $item['id_produk'];
        $id_u = $item['id_ukuran'];
        $qty = $item['jumlah'];
        query("UPDATE stok_produk SET stok = stok + $qty WHERE id_produk = $id_p AND id_ukuran = $id_u");
    }
    
    // 3. Update status transaksi
    query("UPDATE transaksi SET status = 'dibatalkan' WHERE id_transaksi = $id_trx");
    echo "<script>alert('Transaksi Berhasil Dibatalkan. Stok dikembalikan!'); window.location.href='?page=transaksi';</script>";
}

$range = isset($_GET['range']) ? $_GET['range'] : 'all';
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '';

// Handle Presets like Dashboard/Laporan
if ($range != 'all' || (empty($tgl_mulai) && $range == 'all')) {
    switch ($range) {
        case 'today':
            $tgl_mulai = date('Y-m-d');
            $tgl_akhir = date('Y-m-d');
            break;
        case 'week':
            $tgl_mulai = date('Y-m-d', strtotime('-7 days'));
            $tgl_akhir = date('Y-m-d');
            break;
        case 'month':
            $tgl_mulai = date('Y-m-01');
            $tgl_akhir = date('Y-m-t');
            break;
        case 'year':
            $tgl_mulai = date('Y-01-01');
            $tgl_akhir = date('Y-12-31');
            break;
    }
}

$where_clauses = [];
if ($filter == 'selesai') $where_clauses[] = "status = 'selesai'";
if ($filter == 'diproses') $where_clauses[] = "status = 'diproses'";
if ($filter == 'batal') $where_clauses[] = "status = 'dibatalkan'";

if ($tgl_mulai && $tgl_akhir) {
    $where_clauses[] = "DATE(tanggal) BETWEEN '$tgl_mulai' AND '$tgl_akhir'";
} elseif ($tgl_mulai) {
    $where_clauses[] = "DATE(tanggal) >= '$tgl_mulai'";
} elseif ($tgl_akhir) {
    $where_clauses[] = "DATE(tanggal) <= '$tgl_akhir'";
}

$query_trx = "SELECT * FROM transaksi";
if (!empty($where_clauses)) {
    $query_trx .= " WHERE " . implode(" AND ", $where_clauses);
}
$query_trx .= " ORDER BY tanggal DESC";

$transactions = query($query_trx);

// Function to get items for a transaction
function getTrxItems($id) {
    return query("SELECT dt.*, p.nama_produk, u.ukuran 
                  FROM detail_transaksi dt 
                  JOIN produk_tarumpah p ON dt.id_produk = p.id_produk 
                  JOIN ukuran u ON dt.id_ukuran = u.id_ukuran 
                  WHERE dt.id_transaksi = $id");
}
?>

<div class="space-y-4">

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-1 overflow-x-auto no-scrollbar">
        <a href="?page=transaksi&status=all&range=<?php echo $range; ?>" class="px-4 py-2 text-xs font-bold whitespace-nowrap transition-all <?php echo $filter == 'all' ? 'border-b-2 border-[#3e2723] text-[#3e2723]' : 'text-gray-400 hover:text-[#3e2723]' ?>">Semua</a>
        <a href="?page=transaksi&status=diproses&range=<?php echo $range; ?>" class="px-4 py-2 text-xs font-bold whitespace-nowrap transition-all <?php echo $filter == 'diproses' ? 'border-b-2 border-[#3e2723] text-[#3e2723]' : 'text-gray-400 hover:text-[#3e2723]' ?>">Online (Diproses)</a>
        <a href="?page=transaksi&status=selesai&range=<?php echo $range; ?>" class="px-4 py-2 text-xs font-bold whitespace-nowrap transition-all <?php echo $filter == 'selesai' ? 'border-b-2 border-[#3e2723] text-[#3e2723]' : 'text-gray-400 hover:text-[#3e2723]' ?>">Selesai</a>
        <a href="?page=transaksi&status=batal&range=<?php echo $range; ?>" class="px-4 py-2 text-xs font-bold whitespace-nowrap transition-all <?php echo $filter == 'batal' ? 'border-b-2 border-[#3e2723] text-[#3e2723]' : 'text-gray-400 hover:text-[#3e2723]' ?>">Dibatalkan</a>
    </div>

    <!-- Date Filter Bar -->
    <div class="card-gradient p-3 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                <input type="hidden" name="page" value="transaksi">
                <input type="hidden" name="status" value="<?php echo $filter; ?>">
                <input type="hidden" name="range" value="all">
                
                <div class="space-y-1 flex-1 min-w-[120px]">
                    <label class="text-[9px] text-gray-400 uppercase font-black tracking-widest pl-1">Dari</label>
                    <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>" class="w-full bg-white border border-gray-200 rounded-lg py-1.5 px-2 text-xs focus:outline-none focus:border-[#3e2723] text-[#3e2723] font-bold">
                </div>

                <div class="space-y-1 flex-1 min-w-[120px]">
                    <label class="text-[9px] text-gray-400 uppercase font-black tracking-widest pl-1">Sampai</label>
                    <input type="date" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>" class="w-full bg-white border border-gray-200 rounded-lg py-1.5 px-2 text-xs focus:outline-none focus:border-[#3e2723] text-[#3e2723] font-bold">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-[#3e2723] text-white px-4 py-1.5 rounded-lg text-[10px] font-bold shadow-md hover:bg-[#5d4037] transition-all flex items-center gap-1.5">
                        <i class="ph ph-funnel"></i> FILTER
                    </button>
                    <?php if($tgl_mulai || $tgl_akhir): ?>
                        <a href="?page=transaksi&status=<?php echo $filter; ?>" class="bg-white border border-gray-200 text-rose-500 px-3 py-1.5 rounded-lg text-[10px] font-bold hover:bg-rose-50 transition-all">RESET</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="flex gap-2 border-l border-gray-100 pl-3">
                <?php 
                    $range_name = ($tgl_mulai ? $tgl_mulai : 'Awal') . '_sd_' . ($tgl_akhir ? $tgl_akhir : 'Sekarang');
                ?>
                <button onclick="exportTableToExcel('hidden_trx_table', 'Transaksi_<?php echo $filter; ?>_<?php echo $range_name; ?>')" class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-bold border border-emerald-100 hover:bg-emerald-100 transition-all">
                    <i class="ph ph-file-xls text-sm"></i> EXCEL
                </button>
                <button onclick="exportTableToPDF('hidden_trx_table', 'Daftar Transaksi (<?php echo strtoupper($filter); ?>) <?php echo $range_name; ?>', 'Transaksi_<?php echo $filter; ?>_<?php echo $range_name; ?>')" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-bold border border-rose-100 hover:bg-rose-100 transition-all">
                    <i class="ph ph-file-pdf text-sm"></i> PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Preset Date Filters -->
    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1">
        <a href="?page=transaksi&status=<?php echo $filter; ?>&range=all" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?php echo $range == 'all' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Semua
        </a>
        <a href="?page=transaksi&status=<?php echo $filter; ?>&range=today" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?php echo $range == 'today' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Hari Ini
        </a>
        <a href="?page=transaksi&status=<?php echo $filter; ?>&range=week" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?php echo $range == 'week' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            7 Hari
        </a>
        <a href="?page=transaksi&status=<?php echo $filter; ?>&range=month" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?php echo $range == 'month' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Bulan Ini
        </a>
        <a href="?page=transaksi&status=<?php echo $filter; ?>&range=year" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?php echo $range == 'year' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Tahun Ini
        </a>
    </div>

    <!-- Hidden Table for Export -->
    <table id="hidden_trx_table" class="hidden">
        <thead>
            <tr>
                <th>ID TRX</th>
                <th>TANGGAL</th>
                <th>METODE</th>
                <th>STATUS</th>
                <th>TOTAL</th>
                <th>ITEMS</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            $count_online = 0;
            $count_offline = 0;

            foreach($transactions as $t): 
                $items = getTrxItems($t['id_transaksi']);
                $item_str = "";
                foreach($items as $it) { $item_str .= $it['nama_produk'] . " (" . $it['ukuran'] . ") x" . $it['jumlah'] . ", "; }
                
                $is_selesai = ($t['status'] == 'selesai');
                $realized_income = $is_selesai ? (float)$t['total_harga'] : 0;
                $grand_total += $realized_income;

                if ($is_selesai) {
                    if (strtolower($t['metode_pembayaran']) == 'online') {
                        $count_online++;
                    } else {
                        $count_offline++;
                    }
                }
            ?>
            <tr>
                <td>#TRX-<?php echo $t['id_transaksi']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($t['tanggal'])); ?></td>
                <td><?php echo strtoupper($t['metode_pembayaran']); ?></td>
                <td><?php echo strtoupper($t['status']); ?></td>
                <td><?php echo $realized_income; ?></td>
                <td><?php echo rtrim($item_str, ', '); ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- Summary Row for Export -->
            <tr style="font-weight: bold; background-color: #f0f0f0; border-top: 1px solid #000;">
                <td colspan="2" style="text-align: right;">TOTAL:</td>
                <td>ON: <?php echo $count_online; ?> | OFF: <?php echo $count_offline; ?></td>
                <td style="text-align: right;">PENDAPATAN:</td>
                <td><?php echo $grand_total; ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Transaction List -->
    <div class="grid grid-cols-1 gap-4">
        <?php if(empty($transactions)): ?>
            <div class="card-gradient p-10 text-center text-gray-400 italic">
                Belum ada transaksi pada kategori ini.
            </div>
        <?php else: foreach($transactions as $t): 
            $items = getTrxItems($t['id_transaksi']);
            $is_pending = ($t['status'] == 'diproses');
            $is_cancelled = ($t['status'] == 'dibatalkan');
            
            $status_label = $is_pending ? 'Diproses (Online)' : ($is_cancelled ? 'Dibatalkan' : 'Selesai');
            $status_class = $is_pending ? 'bg-amber-100 text-amber-700 border-amber-200' : ($is_cancelled ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200');
            $icon_class = $is_pending ? 'ph-bold ph-clock text-amber-600' : ($is_cancelled ? 'ph-bold ph-x-circle text-rose-600' : 'ph-bold ph-check-circle text-emerald-600');
            $bg_icon = $is_pending ? 'bg-amber-50' : ($is_cancelled ? 'bg-rose-50' : 'bg-emerald-50');
            $border_class = $is_pending ? 'border-amber-500' : ($is_cancelled ? 'border-rose-500' : 'border-emerald-500');
        ?>
        <div class="card-gradient rounded-2xl p-2 relative group border-l-4 <?php echo $border_class; ?> hover:shadow-md transition-all">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl <?php echo $bg_icon; ?> flex items-center justify-center border border-gray-100 flex-shrink-0">
                        <i class="<?php echo $icon_class; ?> text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h4 class="font-bold text-[#1a1a1a] text-sm">#TRX-<?php echo $t['id_transaksi']; ?></h4>
                            <span class="px-1.5 py-[1px] rounded text-[9px] font-bold uppercase tracking-wider <?php echo $status_class; ?>">
                                <?php echo $status_label; ?>
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-500 font-medium"><?php echo date('d M, H:i', strtotime($t['tanggal'])); ?> • <?php echo strtoupper($t['metode_pembayaran']); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between md:justify-end gap-4">
                    <div class="text-left md:text-right">
                        <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-0.5">Total</p>
                        <p class="text-sm font-black text-[#3e2723]">Rp <?php echo number_format($t['total_harga'], 0, ',', '.'); ?></p>
                    </div>
                    
                    <div class="flex gap-1.5 text-xs font-bold">
                        <?php if($is_pending): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="selesaikan_trx">
                                <input type="hidden" name="id_transaksi" value="<?php echo $t['id_transaksi']; ?>">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#3e2723] hover:bg-[#5d4037] text-white transition-all flex items-center gap-1.5 shadow-sm text-[10px]">
                                    <i class="ph ph-check-circle"></i> Selesai
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Batalkan pesanan?')">
                                <input type="hidden" name="action" value="batalkan_trx">
                                <input type="hidden" name="id_transaksi" value="<?php echo $t['id_transaksi']; ?>">
                                <button type="submit" class="p-1.5 rounded-lg bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 transition-all flex items-center gap-1.5 shadow-sm">
                                    <i class="ph ph-x-circle text-base"></i>
                                </button>
                            </form>
                        <?php elseif(!$is_cancelled): ?>
                            <button onclick="window.open('print_receipt.php?id=<?php echo $t['id_transaksi']; ?>', '_blank', 'width=600,height=800')" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-[#3e2723] hover:bg-gray-50 transition-all flex items-center gap-1.5 shadow-sm text-[10px]">
                                <i class="ph ph-printer"></i> Struk
                            </button>
                        <?php endif; ?>
                        <button class="p-1.5 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-[#3e2723] hover:border-[#3e2723] transition-all shadow-sm">
                            <i class="ph ph-eye text-base"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Items Detail -->
            <div class="mt-2 pt-2 border-t border-gray-100 flex flex-wrap gap-2">
                <?php foreach($items as $item): ?>
                <div class="text-[9px] text-gray-500 font-bold bg-gray-50/50 px-2 py-1 rounded-md border border-gray-100 flex items-center gap-1.5">
                    <span class="w-1 h-1 rounded-full bg-[#3e2723]"></span>
                    <?php echo $item['nama_produk']; ?> (<?php echo $item['ukuran']; ?>) <span class="text-[#3e2723]">x<?php echo $item['jumlah']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
