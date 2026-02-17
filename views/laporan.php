<?php
// views/laporan.php
$range = isset($_GET['range']) ? $_GET['range'] : 'all';
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '';

// Handle Presets like Dashboard
if ($range != 'all' || empty($tgl_mulai)) {
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
        default:
            if (empty($tgl_mulai)) {
                $tgl_mulai = date('Y-m-01');
                $tgl_akhir = date('Y-m-d');
            }
            break;
    }
}

// --- HANDLE DELETE ACTIONS ---
if (isset($_GET['action_delete'])) {
    $id_del = (int)$_GET['id'];
    $type = $_GET['action_delete']; // 'pembelian' or 'transaksi'

    if ($type == 'pembelian') {
        // 1. Ambil detail untuk pembalikan stok
        $details = query("SELECT id_produk, id_ukuran, jumlah FROM detail_pembelian WHERE id_pembelian = $id_del");
        foreach ($details as $d) {
            $id_p = $d['id_produk'];
            $id_u = $d['id_ukuran'];
            $qty = $d['jumlah'];
            query("UPDATE stok_produk SET stok = stok - $qty WHERE id_produk = $id_p AND id_ukuran = $id_u");
        }
        // 2. Hapus data utama (detail otomatis terhapus karena CASCADE)
        query("DELETE FROM pembelian WHERE id_pembelian = $id_del");
        echo "<script>alert('Data restok berhasil dihapus dan stok telah dibatalkan.'); window.location.href='?page=laporan&tgl_mulai=$tgl_mulai&tgl_akhir=$tgl_akhir';</script>";
    } 
    elseif ($type == 'transaksi') {
        // 1. Ambil detail untuk pembalikan stok (produk dikembalikan ke stok)
        $details = query("SELECT id_produk, id_ukuran, jumlah FROM detail_transaksi WHERE id_transaksi = $id_del");
        foreach ($details as $d) {
            $id_p = $d['id_produk'];
            $id_u = $d['id_ukuran'];
            $qty = $d['jumlah'];
            query("UPDATE stok_produk SET stok = stok + $qty WHERE id_produk = $id_p AND id_ukuran = $id_u");
        }
        // 2. Hapus data utama
        query("DELETE FROM transaksi WHERE id_transaksi = $id_del");
        echo "<script>alert('Data transaksi berhasil dihapus dan produk dikembalikan ke stok.'); window.location.href='?page=laporan&tgl_mulai=$tgl_mulai&tgl_akhir=$tgl_akhir';</script>";
    }
}
// -----------------------------

// Query untuk statistik utama
$where_date = "status = 'selesai' AND tanggal BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'";
$total_omzet = sumColumn('transaksi', 'total_harga', "WHERE $where_date");
$total_trx = countRows('transaksi', "WHERE $where_date");

// Query Hitung Modal & Laba (Berdasarkan Total Belanja Restok)
// User Request: "sesuaikan total modal itu dengan dana restok"
$where_purc = "tanggal BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'";
$total_pengeluaran_stok = (float)sumColumn('pembelian', 'total_pembelian', "WHERE $where_purc");

$total_modal = $total_pengeluaran_stok; 
$total_laba = $total_omzet - $total_modal;

// Top Selling Products
$top_products = query("SELECT p.nama_produk, SUM(dt.jumlah) as total_terjual 
                       FROM detail_transaksi dt 
                       JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
                       JOIN produk_tarumpah p ON dt.id_produk = p.id_produk
                       WHERE t.status = 'selesai' AND t.tanggal BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'
                       GROUP BY p.id_produk ORDER BY total_terjual DESC LIMIT 5");

// Metode Pembayaran Stat
$payment_stats = query("SELECT metode_pembayaran, COUNT(*) as jumlah, SUM(total_harga) as total 
                        FROM transaksi 
                        WHERE $where_date 
                        GROUP BY metode_pembayaran");

// NEW: Detailed Logs for Sales
$sales_logs = query("SELECT t.*, GROUP_CONCAT(CONCAT(p.nama_produk, ' (', u.ukuran, ') x', dt.jumlah) SEPARATOR ', ') as items
                    FROM transaksi t
                    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                    JOIN produk_tarumpah p ON dt.id_produk = p.id_produk
                    JOIN ukuran u ON dt.id_ukuran = u.id_ukuran
                    WHERE t.status = 'selesai' AND t.tanggal BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'
                    GROUP BY t.id_transaksi
                    ORDER BY t.tanggal DESC");

// NEW: Detailed Logs for Restocks
$restock_logs = query("SELECT pb.*, s.nama_supplier, GROUP_CONCAT(CONCAT(p.nama_produk, ' (', u.ukuran, ') x', dp.jumlah) SEPARATOR ', ') as items
                      FROM pembelian pb
                      LEFT JOIN supplier s ON pb.id_supplier = s.id_supplier
                      LEFT JOIN detail_pembelian dp ON pb.id_pembelian = dp.id_pembelian
                      LEFT JOIN produk_tarumpah p ON dp.id_produk = p.id_produk
                      LEFT JOIN ukuran u ON dp.id_ukuran = u.id_ukuran
                      WHERE pb.tanggal BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'
                      GROUP BY pb.id_pembelian
                      ORDER BY pb.tanggal DESC");

// Filter Logs into two categories
$prod_restock_logs = array_filter($restock_logs, function($l) { return !empty($l['items']); });
$material_logs = array_filter($restock_logs, function($l) { return (float)$l['biaya_tambahan'] > 0; });

// RESTORED: Summary Variables for Restock Analysis
$where_purc = "tanggal BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'";
$total_pengeluaran_stok = sumColumn('pembelian', 'total_pembelian', "WHERE $where_purc");
$restock_stats = query("SELECT s.nama_supplier, COUNT(p.id_pembelian) as frekuensi, SUM(p.total_pembelian) as total_biaya 
                        FROM pembelian p 
                        LEFT JOIN supplier s ON p.id_supplier = s.id_supplier 
                        WHERE p.$where_purc 
                        GROUP BY p.id_supplier 
                        ORDER BY total_biaya DESC");
?>

<div class="space-y-8">


    <!-- Custom Date Filter Bar -->
    <div class="card-gradient p-3 rounded-xl shadow-sm border border-gray-100 h-fit">
        <form method="GET" class="flex flex-col md:flex-row items-end gap-3">
            <input type="hidden" name="page" value="laporan">
            <input type="hidden" name="range" value="all">
            <div class="space-y-1 flex-1 w-full md:w-auto">
                <label class="text-[9px] text-gray-400 uppercase font-black tracking-[0.2em] pl-1">Rentang Mulai</label>
                <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>" class="w-full bg-white border border-gray-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:border-[#3e2723] font-bold text-[#3e2723]">
            </div>
            <div class="space-y-1 flex-1 w-full md:w-auto">
                <label class="text-[9px] text-gray-400 uppercase font-black tracking-[0.2em] pl-1">Rentang Akhir</label>
                <input type="date" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>" class="w-full bg-white border border-gray-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:border-[#3e2723] font-bold text-[#3e2723]">
            </div>
            <button type="submit" class="btn-primary px-4 py-1.5 rounded-lg font-bold text-[10px] uppercase tracking-widest flex items-center gap-1.5 shadow-md w-full md:w-auto justify-center">
                <i class="ph-bold ph-funnel-simple"></i> FILTER
            </button>
        </form>
    </div>


    <!-- Summary Grid -->
    <!-- Hidden Summary Table for Full Export -->
    <table id="table_summary" class="hidden">
        <thead>
            <tr>
                <th colspan="2">RINGKASAN LAPORAN KEUANGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Pemasukan (Omzet)</td>
                <td>Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Total Pengeluaran (Belanja Stok & Bahan)</td>
                <td>Rp <?php echo number_format($total_pengeluaran_stok, 0, ',', '.'); ?></td>
            </tr>
            <tr style="font-weight: bold;">
                <td>Estimasi Laba Bersih</td>
                <td>Rp <?php echo number_format($total_laba, 0, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card-gradient p-8 rounded-[2rem] relative overflow-hidden group border-b-4 border-emerald-500">
            <div class="relative z-10">
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mb-2">Total Omzet</p>
                <h3 class="text-3xl font-black text-[#1a1a1a] mb-2">Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></h3>
                <div class="flex items-center gap-2 text-emerald-600 text-xs font-bold">
                    <i class="ph-bold ph-arrow-up-right"></i>
                    <span>Total Penjualan Kotor</span>
                </div>
            </div>
            <i class="ph-bold ph-coins absolute -right-6 -bottom-6 text-8xl text-emerald-500/10 group-hover:rotate-12 transition-transform duration-700"></i>
        </div>

        <div class="card-gradient p-8 rounded-[2rem] relative overflow-hidden group border-b-4 border-rose-400">
            <div class="relative z-10">
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mb-2">Total Modal</p>
                <h3 class="text-3xl font-black text-[#1a1a1a] mb-2">Rp <?php echo number_format($total_modal, 0, ',', '.'); ?></h3>
                <div class="flex items-center gap-2 text-rose-500 text-xs font-bold">
                    <i class="ph-bold ph-shopping-cart"></i>
                    <span>HPP (Harga Pokok Penjualan)</span>
                </div>
            </div>
            <i class="ph-bold ph-hand-coins absolute -right-6 -bottom-6 text-8xl text-rose-500/10 group-hover:-rotate-12 transition-transform duration-700"></i>
        </div>

        <div class="card-gradient p-8 rounded-[2rem] bg-[#3e2723] text-white relative overflow-hidden group shadow-2xl shadow-[#3e2723]/20">
            <div class="relative z-10">
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mb-2">Laba Bersih</p>
                <h3 class="text-3xl font-black mb-2">Rp <?php echo number_format($total_laba, 0, ',', '.'); ?></h3>
                <div class="flex items-center gap-2 text-[#d4a373] text-xs font-bold">
                    <i class="ph-bold ph-sparkle"></i>
                    <span>Keuntungan Bersih</span>
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
            <i class="ph-bold ph-chart-line-up absolute -right-6 -bottom-6 text-8xl text-white/20 group-hover:translate-x-2 group-hover:-translate-y-2 transition-transform duration-700"></i>
        </div>
    </div>


    <!-- Payment Methods Row -->
    <div class="card-gradient rounded-[2rem] p-8">
        <h3 class="text-lg font-black uppercase tracking-tighter text-[#3e2723] mb-8">Statistik Pembayaran & Arus Kas</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach($payment_stats as $ps): 
                $is_online = $ps['metode_pembayaran'] == 'online';
            ?>
            <div class="p-6 rounded-3xl <?php echo $is_online ? 'bg-indigo-50 border-indigo-100' : 'bg-orange-50 border-orange-100'; ?> border flex flex-col justify-between relative overflow-hidden group">
                <div class="relative z-10">
                    <i class="ph-bold <?php echo $is_online ? 'ph-qr-code text-indigo-500' : 'ph-money text-orange-500'; ?> text-2xl mb-4"></i>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest"><?php echo $ps['metode_pembayaran']; ?></p>
                    <h4 class="text-lg font-black text-[#1a1a1a] mt-1">Rp <?php echo number_format($ps['total'], 0, ',', '.'); ?></h4>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-gray-500 mt-4"><?php echo $ps['jumlah']; ?> Transaksi</p>
                </div>
                <i class="ph-bold <?php echo $is_online ? 'ph-qr-code text-indigo-500/10' : 'ph-money text-orange-500/10'; ?> absolute -right-4 -bottom-4 text-6xl group-hover:scale-110 transition-transform duration-700"></i>
            </div>
            <?php endforeach; ?>
            
            <!-- Overall Summary Card for Restock -->
            <div class="p-6 rounded-3xl bg-rose-50 border-rose-100 border flex flex-col justify-between relative overflow-hidden group">
                <div class="relative z-10">
                    <i class="ph-bold ph-trend-down text-rose-500 text-2xl mb-4"></i>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Dana Restok</p>
                    <h4 class="text-lg font-black text-rose-600 mt-1">Rp <?php echo number_format($total_pengeluaran_stok, 0, ',', '.'); ?></h4>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-rose-400 mt-4">Uang Keluar</p>
                </div>
                <i class="ph-bold ph-trend-down text-rose-500/10 absolute -right-4 -bottom-4 text-6xl group-hover:scale-110 transition-transform duration-700"></i>
            </div>
        </div>
        <?php if(empty($payment_stats) && $total_pengeluaran_stok == 0): ?>
            <p class="text-center py-10 text-gray-400 italic">Belum ada data aktivitas keuangan.</p>
        <?php endif; ?>
    </div>

    <!-- Global Print/Export Action (FULL WIDTH COMPACT BAR) -->
    <div class="no-print -mt-4 mb-12">
        <div class="bg-[#3e2723] px-8 py-3 rounded-2xl shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 border border-white/10 w-full">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center">
                    <i class="ph-fill ph-printer text-xs text-white/70"></i>
                </div>
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white/50">Export Laporan Operasional Lengkap</span>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="exportFullReportPDF(['table_summary', 'table_sales', 'table_restock', 'table_materials'], ['Ringkasan Keuangan', 'Laporan Penjualan', 'Laporan Restok Produk', 'Laporan Biaya Bahan'], 'Laporan Lengkap Tarumpah Toha (<?php echo $tgl_mulai; ?> - <?php echo $tgl_akhir; ?>)', 'Laporan_Keseluruhan_Tarumpah_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" 
                        class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-200 border border-rose-500/30 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all flex items-center gap-2 group">
                    <i class="ph-fill ph-file-pdf text-sm group-hover:scale-110 transition-transform"></i> PDF LENGKAP
                </button>
                <button onclick="exportFullReportExcel(['table_summary', 'table_sales', 'table_restock', 'table_materials'], ['Ringkasan', 'Penjualan', 'Restok_Sepatu', 'Bahan_Baku'], 'Laporan_Keseluruhan_Tarumpah_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" 
                        class="px-4 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-200 border border-emerald-500/30 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all flex items-center gap-2 group">
                    <i class="ph-fill ph-file-xls text-sm group-hover:scale-110 transition-transform"></i> EXCEL LENGKAP
                </button>
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-200 border border-amber-500/30 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all flex items-center gap-2 group">
                    <i class="ph-bold ph-printer text-sm group-hover:scale-110 transition-transform"></i> CETAK KERTAS
                </button>
        </div>
    </div>

    <!-- Preset Date Filters -->
    <div class="flex items-center justify-center gap-2 overflow-x-auto no-scrollbar pb-1 mb-4">
        <a href="?page=laporan&range=all" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all <?php echo $range == 'all' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Semua Waktu
        </a>
        <a href="?page=laporan&range=today" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all <?php echo $range == 'today' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Hari Ini
        </a>
        <a href="?page=laporan&range=week" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all <?php echo $range == 'week' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            7 Hari Terakhir
        </a>
        <a href="?page=laporan&range=month" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all <?php echo $range == 'month' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Bulan Ini
        </a>
        <a href="?page=laporan&range=year" class="px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all <?php echo $range == 'year' ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
            Tahun Ini
        </a>
    </div>

    <!-- Hidden Title for Printer -->
    <div class="print-title">
        <h1 style="font-size: 20px; font-weight: 900; text-transform: uppercase;">Laporan Operasional Tarumpah Toha Tasik</h1>
        <p style="font-size: 11px; margin-top: 3px;">Periode: <?php echo date('d M Y', strtotime($tgl_mulai)); ?> s/d <?php echo date('d M Y', strtotime($tgl_akhir)); ?></p>
        <hr style="margin: 15px 0; border: 1px solid #000;">
    </div>

    <!-- Detailed Transaction Logs -->
    <div class="grid grid-cols-1 gap-8 mt-4">
        <!-- Sales Log -->
        <div class="card-gradient rounded-[2rem] p-8">
            <h3 class="text-lg font-black uppercase tracking-tighter text-[#3e2723] mb-6 flex items-center gap-3">
                <i class="ph ph-shopping-bag"></i> Catatan Penjualan (Kasir)
                <div class="ml-auto flex gap-2">
                    <button onclick="exportTableToExcel('table_sales', 'Laporan_Penjualan_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-bold border border-emerald-100 hover:bg-emerald-100 transition-all flex items-center gap-1.5">
                        <i class="ph ph-file-xls text-sm"></i> Excel
                    </button>
                    <button onclick="exportTableToPDF('table_sales', 'Laporan Penjualan (<?php echo $tgl_mulai; ?> - <?php echo $tgl_akhir; ?>)', 'Laporan_Penjualan_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-bold border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-1.5">
                        <i class="ph ph-file-pdf text-sm"></i> PDF
                    </button>
                </div>
            </h3>
            <div class="overflow-x-auto">
                <table id="table_sales" class="w-full text-left text-xs border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-gray-400 uppercase font-black tracking-widest">
                            <th class="px-4 py-2">Waktu</th>
                            <th class="px-4 py-2">Produk</th>
                            <th class="px-4 py-2">Metode</th>
                            <th class="px-4 py-2 text-right">Total</th>
                            <th class="px-4 py-2 text-center no-export">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($sales_logs)): ?>
                            <tr><td colspan="5" class="text-center py-6 text-gray-400 italic">Tidak ada catatan penjualan.</td></tr>
                        <?php else: 
                            $grand_total_sales = 0;
                            $count_online = 0;
                            $count_offline = 0;
                            $nominal_online = 0;
                            $nominal_offline = 0;
                            foreach($sales_logs as $log): 
                                $grand_total_sales += $log['total_harga'];
                                if($log['metode_pembayaran'] == 'online') {
                                    $count_online++;
                                    $nominal_online += $log['total_harga'];
                                } else {
                                    $count_offline++;
                                    $nominal_offline += $log['total_harga'];
                                }
                        ?>
                            <tr class="bg-gray-50/50 rounded-xl">
                                <td class="px-4 py-3 font-bold text-gray-500 whitespace-nowrap"><?php echo date('d/m H:i', strtotime($log['tanggal'])); ?></td>
                                <td class="px-4 py-3 font-medium text-gray-600"><?php echo $log['items']; ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase <?php echo $log['metode_pembayaran'] == 'online' ? 'bg-indigo-100 text-indigo-600' : 'bg-orange-100 text-orange-600'; ?>">
                                        <?php echo $log['metode_pembayaran']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-black text-[#3e2723] text-right whitespace-nowrap">Rp <?php echo number_format($log['total_harga'], 0, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-center no-export">
                                    <a href="?page=laporan&action_delete=transaksi&id=<?php echo $log['id_transaksi']; ?>&tgl_mulai=<?php echo $tgl_mulai; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>" 
                                       onclick="return confirm('Hapus transaksi ini? Stok akan dikembalikan.')"
                                       class="text-rose-500 hover:text-rose-700 transition-colors">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                            <tr class="border-t-2 border-dashed border-gray-100">
                                <td colspan="3" class="px-4 py-4 text-[10px] font-black uppercase text-gray-400 text-right tracking-widest">Total Penjualan Kasir</td>
                                <td class="px-4 py-4 font-black text-[#3e2723] text-right text-sm">Rp <?php echo number_format($grand_total_sales, 0, ',', '.'); ?></td>
                                <td class="no-export"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-[10px] font-black uppercase text-gray-400 text-right tracking-widest pt-0">Detail Transaksi</td>
                                <td class="px-4 py-2 text-right pt-0">
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="text-[9px] font-black text-indigo-500 uppercase">ONLINE: <?php echo $count_online; ?> TRX (Rp <?php echo number_format($nominal_online, 0, ',', '.'); ?>)</span>
                                        <span class="text-[9px] font-black text-orange-500 uppercase">OFFLINE: <?php echo $count_offline; ?> TRX (Rp <?php echo number_format($nominal_offline, 0, ',', '.'); ?>)</span>
                                    </div>
                                </td>
                                <td class="no-export"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Restock Produk Log -->
        <div class="card-gradient rounded-[2rem] p-8">
            <h3 class="text-lg font-black uppercase tracking-tighter text-[#3e2723] mb-6 flex items-center gap-3">
                <i class="ph ph-box-arrow-down"></i> Catatan Restok Produk (Sepatu)
                <div class="ml-auto flex gap-2">
                    <button onclick="exportTableToExcel('table_restock', 'Laporan_Restok_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-bold border border-emerald-100 hover:bg-emerald-100 transition-all flex items-center gap-1.5">
                        <i class="ph ph-file-xls text-sm"></i> Excel
                    </button>
                    <button onclick="exportTableToPDF('table_restock', 'Laporan Restok (<?php echo $tgl_mulai; ?> - <?php echo $tgl_akhir; ?>)', 'Laporan_Restok_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-bold border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-1.5">
                        <i class="ph ph-file-pdf text-sm"></i> PDF
                    </button>
                </div>
            </h3>
            <div class="overflow-x-auto">
                <table id="table_restock" class="w-full text-left text-xs border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-gray-400 uppercase font-black tracking-widest">
                            <th class="px-4 py-2">Waktu</th>
                            <th class="px-4 py-2">Detail Sepatu</th>
                            <th class="px-4 py-2 text-right">Biaya Restok</th>
                            <th class="px-4 py-2 text-center no-export">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($prod_restock_logs)): ?>
                            <tr><td colspan="3" class="text-center py-6 text-gray-400 italic">Tidak ada restok produk.</td></tr>
                        <?php else: 
                            $grand_total_restock = 0;
                            foreach($prod_restock_logs as $log): 
                                $restock_only_cost = (float)$log['total_pembelian'] - (float)$log['biaya_tambahan'];
                                $grand_total_restock += $restock_only_cost;
                        ?>
                            <tr class="bg-gray-50/50 rounded-xl">
                                <td class="px-4 py-3 font-bold text-gray-500 whitespace-nowrap"><?php echo date('d/m H:i', strtotime($log['tanggal'])); ?></td>
                                <td class="px-4 py-3">
                                    <p class="text-xs font-bold text-[#1a1a1a] mb-0.5"><?php echo $log['items']; ?></p>
                                    <p class="text-[10px] font-black text-gray-400"><?php echo $log['nama_supplier'] ?? 'Umum'; ?></p>
                                </td>
                                <td class="px-4 py-3 font-black text-[#3e2723] text-right whitespace-nowrap">Rp <?php echo number_format($restock_only_cost, 0, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-center no-export">
                                    <a href="?page=laporan&action_delete=pembelian&id=<?php echo $log['id_pembelian']; ?>&tgl_mulai=<?php echo $tgl_mulai; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>" 
                                       onclick="return confirm('Hapus riwayat restok ini? Stok akan dikurangi kembali.')"
                                       class="text-rose-500 hover:text-rose-700 transition-colors">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                            <tr class="border-t-2 border-dashed border-gray-100">
                                <td colspan="2" class="px-4 py-4 text-[10px] font-black uppercase text-gray-400 text-right tracking-widest">Total Belanja Sepatu</td>
                                <td class="px-4 py-4 font-black text-[#3e2723] text-right text-sm">Rp <?php echo number_format($grand_total_restock, 0, ',', '.'); ?></td>
                                <td class="no-export"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Material Log -->
        <div class="card-gradient rounded-[2rem] p-8 bg-rose-50/20 border border-rose-100/50">
            <h3 class="text-lg font-black uppercase tracking-tighter text-rose-600 mb-6 flex items-center gap-3">
                <i class="ph ph-package"></i> Catatan Bahan Baku & Biaya Ekstra
                <div class="ml-auto flex gap-2">
                    <button onclick="exportTableToExcel('table_materials', 'Laporan_Bahan_Baku_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-bold border border-emerald-100 hover:bg-emerald-100 transition-all flex items-center gap-1.5 no-export">
                        <i class="ph ph-file-xls text-sm"></i> Excel
                    </button>
                    <button onclick="exportTableToPDF('table_materials', 'Laporan Biaya Bahan (<?php echo $tgl_mulai; ?> - <?php echo $tgl_akhir; ?>)', 'Laporan_Bahan_Baku_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>')" class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-bold border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-1.5 no-export">
                        <i class="ph ph-file-pdf text-sm"></i> PDF
                    </button>
                </div>
            </h3>
            <div class="overflow-x-auto">
                <table id="table_materials" class="w-full text-left text-xs border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-gray-400 uppercase font-black tracking-widest">
                            <th class="px-4 py-2">Waktu</th>
                            <th class="px-4 py-2">Catatan Bahan / Alasan</th>
                            <th class="px-4 py-2 text-right">Biaya Bahan</th>
                            <th class="px-4 py-2 text-center no-export">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($material_logs)): ?>
                            <tr><td colspan="3" class="text-center py-6 text-gray-400 italic">Tidak ada catatan bahan baku.</td></tr>
                        <?php else: 
                            $grand_total_materials = 0;
                            foreach($material_logs as $log): 
                                $grand_total_materials += (float)$log['biaya_tambahan'];
                        ?>
                            <tr class="bg-white rounded-xl shadow-sm border border-rose-100">
                                <td class="px-4 py-3 font-bold text-gray-400 whitespace-nowrap"><?php echo date('d/m H:i', strtotime($log['tanggal'])); ?></td>
                                <td class="px-4 py-3">
                                    <p class="text-xs font-black text-rose-600 mb-0.5"><?php echo $log['keterangan'] ?: 'Pembelian Bahan Baku'; ?></p>
                                    <p class="text-[10px] font-bold text-gray-400"><?php echo $log['nama_supplier'] ?? 'Umum'; ?></p>
                                </td>
                                <td class="px-4 py-3 font-black text-rose-600 text-right whitespace-nowrap">Rp <?php echo number_format($log['biaya_tambahan'], 0, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-center no-export">
                                    <a href="?page=laporan&action_delete=pembelian&id=<?php echo $log['id_pembelian']; ?>&tgl_mulai=<?php echo $tgl_mulai; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>" 
                                       onclick="return confirm('Hapus catatan biaya bahan baku ini?')"
                                       class="text-rose-500 hover:text-rose-700 transition-colors">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                            <tr class="border-t-2 border-dashed border-rose-200">
                                <td colspan="2" class="px-4 py-4 text-[10px] font-black uppercase text-rose-400 text-right tracking-widest">Total Belanja Bahan</td>
                                <td class="px-4 py-4 font-black text-rose-600 text-right text-sm">Rp <?php echo number_format($grand_total_materials, 0, ',', '.'); ?></td>
                                <td class="no-export"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
