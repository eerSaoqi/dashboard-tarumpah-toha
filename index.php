<?php
// index.php (Main Router)
require_once 'includes/config.php';
require_once 'includes/layout.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Simple Routing
switch ($page) {
    case 'dashboard':
        $title = "Dashboard";
        $viewFile = 'views/dashboard_content.php';
        break;
    case 'kasir':
        $title = "Kasir / Point of Sale";
        $viewFile = 'views/kasir.php';
        break;
    case 'pembelian':
        $title = "Pembelian Stok";
        $viewFile = 'views/pembelian.php';
        break;
    case 'transaksi':
        $title = "Daftar Transaksi";
        $viewFile = 'views/transaksi.php';
        break;
    case 'laporan':
        $title = "Pencatatan Global";
        $viewFile = 'views/laporan.php';
        break;
    case 'produk':
        $title = "Kelola Produk";
        $viewFile = 'views/produk.php';
        break;
    case 'supplier':
        $title = "Kelola Supplier";
        $viewFile = 'views/supplier.php';
        break;
    case 'ukuran':
        $title = "Kelola Ukuran";
        $viewFile = 'views/ukuran.php';
        break;
    case 'kategori':
        $title = "Kelola Kategori";
        $viewFile = 'views/kategori.php';
        break;
    default:
        $title = "404 Not Found";
        $viewFile = null;
        break;
}

ob_start();
if ($viewFile && file_exists($viewFile)) {
    require_once $viewFile;
} else {
    echo "<div class='text-center py-20'><h2 class='text-3xl font-bold'>404</h2><p>Halaman tidak ditemukan.</p></div>";
}
$content = ob_get_clean();

renderLayout($content, $title);
?>
