<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id = (int)$_GET['id'];
$trx = query("SELECT * FROM transaksi WHERE id_transaksi = $id");

if (empty($trx)) {
    die("Transaksi tidak ditemukan.");
}

$t = $trx[0];
$items = query("SELECT dt.*, p.nama_produk, u.ukuran 
                FROM detail_transaksi dt 
                JOIN produk_tarumpah p ON dt.id_produk = p.id_produk 
                JOIN ukuran u ON dt.id_ukuran = u.id_ukuran 
                WHERE dt.id_transaksi = $id");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #TRX-<?php echo $id; ?></title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 72mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 12px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .header { margin-bottom: 10px; }
        .store-name { font-size: 16px; font-weight: bold; }
        .item-row { margin-bottom: 3px; }
        .footer { margin-top: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #f1f1f1; padding: 10px; text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ddd;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #3e2723; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🖨️ CETAK SEKARANG</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #fff; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; margin-left: 10px;">TUTUP</button>
        <p style="font-size: 10px; margin-top: 5px; color: #666;">Jika pratinjau tidak muncul, klik tombol Cetak di atas.</p>
    </div>

    <div class="header text-center">
        <div class="store-name">TOHA 1946</div>
        <div>Komplek Ruko Mayasari Plaza No.17 Tasikmalaya</div>
        <div>Telp: 081214697974</div>
    </div>

    <div class="dashed-line"></div>
    
    <div>
        No: #TRX-<?php echo $id; ?><br>
        Tgl: <?php echo date('d/m/Y H:i', strtotime($t['tanggal'])); ?><br>
        Metode: <?php echo strtoupper($t['metode_pembayaran']); ?>
    </div>

    <div class="dashed-line"></div>

    <table>
        <?php foreach($items as $item): ?>
        <tr>
            <td colspan="3" class="font-bold"><?php echo strtoupper($item['nama_produk']); ?> (<?php echo $item['ukuran']; ?>)</td>
        </tr>
        <tr>
            <td><?php echo $item['jumlah']; ?> x <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
            <td class="text-right"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php
    $total_items = 0;
    foreach($items as $item) {
        $total_items += $item['subtotal'];
    }
    $diskon = $total_items - $t['total_harga'];
    ?>

    <div class="dashed-line"></div>

    <table class="font-bold">
        <tr>
            <td>SUBTOTAL</td>
            <td class="text-right">Rp <?php echo number_format($total_items, 0, ',', '.'); ?></td>
        </tr>
        <?php if ($diskon > 0): ?>
        <tr>
            <td>DISKON</td>
            <td class="text-right">- Rp <?php echo number_format($diskon, 0, ',', '.'); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>TOTAL</td>
            <td class="text-right">Rp <?php echo number_format($t['total_harga'], 0, ',', '.'); ?></td>
        </tr>
    </table>

    <div class="dashed-line"></div>

    <div class="footer text-center">
        *** TERIMA KASIH ***<br>
        Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan.<br>
        Powered by Toha POS
    </div>

    <script>
        // Berikan jeda sedikit agar browser sempat merender layout sebelum print
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
