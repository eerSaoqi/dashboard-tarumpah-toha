<?php
// layout.php
function renderLayout($content, $title = "Dashboard Tarumpah") {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Tarumpah Toha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        <?php echo file_get_contents(dirname(__DIR__) . '/assets/css/style.css'); ?>
        
        /* Loading Animation Styles */
        .loader-wrapper {
            position: fixed;
            top: 0;
            left: 16rem; /* Sidebar Width */
            width: calc(100% - 16rem);
            height: 100%;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.05s ease, visibility 0.05s;
        }
        
        .loader-wrapper.hide {
            opacity: 0;
            visibility: hidden;
        }

        /* Skeleton Loading Styles */
        .skeleton-loader {
            background: #f6f7f8;
            background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-repeat: no-repeat;
            background-size: 800px 100%;
            display: inline-block;
            position: relative;
            animation: shimmer 0.8s infinite linear;
            border-radius: 12px;
        }

        @keyframes shimmer {
            0% { background-position: -468px 0; }
            100% { background-position: 468px 0; }
        }

        .skeleton-card {
            height: 120px;
            width: 100%;
            background: #eee;
        }

        .skeleton-header {
            height: 30px;
            width: 200px;
            margin-bottom: 20px;
        }

        .skeleton-text {
            height: 15px;
            width: 100%;
            margin-bottom: 10px;
        }

        .fade-in-content {
            animation: fadeInUp 0.1s ease-out forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-[#f8f9fa] text-[#1a1a1a]">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar: BROWN -->
        <aside class="w-64 bg-[#3e2723] flex flex-col shadow-2xl flex-shrink-0 flex-grow-0">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <img src="assets/images/logo/logo.png" alt="Logo" class="h-12 w-auto object-contain">
                    <div>
                        <h1 class="font-bold text-lg leading-tight text-white">Tarumpah</h1>
                        <p class="text-[10px] text-[#d4a373] uppercase tracking-widest font-semibold">Toha</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 mt-4 space-y-2">
                <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <i class="ph ph-squares-four text-xl"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="index.php?page=kasir" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo (isset($_GET['page']) && $_GET['page'] == 'kasir') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <i class="ph ph-shopping-cart text-xl"></i>
                    <span class="font-medium">Kasir</span>
                </a>
                <a href="index.php?page=transaksi" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo (isset($_GET['page']) && $_GET['page'] == 'transaksi') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <i class="ph ph-receipt text-xl"></i>
                    <span class="font-medium">Daftar Transaksi</span>
                </a>
                <a href="index.php?page=pembelian" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo (isset($_GET['page']) && $_GET['page'] == 'pembelian') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <i class="ph ph-package text-xl"></i>
                    <span class="font-medium">Tambah Stok</span>
                </a>
                <a href="index.php?page=laporan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo (isset($_GET['page']) && $_GET['page'] == 'laporan') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <i class="ph ph-scroll text-xl"></i>
                    <span class="font-medium">Pencatatan</span>
                </a>
                <div class="pt-6 pb-2 px-4 border-t border-white/5 mt-4">
                    <p class="text-[10px] text-[#d4a373] uppercase font-bold tracking-widest">Master Data</p>
                </div>
                <a href="?page=produk" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo (isset($_GET['page']) && $_GET['page'] == 'produk') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?> transition-all">
                    <i class="ph ph-sneaker text-xl"></i>
                    <span class="font-medium">Produk</span>
                </a>
                <a href="?page=supplier" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo (isset($_GET['page']) && $_GET['page'] == 'supplier') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?> transition-all">
                    <i class="ph ph-users-three text-xl"></i>
                    <span class="font-medium">Supplier</span>
                </a>
                <a href="?page=kategori" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo (isset($_GET['page']) && $_GET['page'] == 'kategori') ? 'sidebar-active shadow-lg' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?> transition-all">
                    <i class="ph ph-tag text-xl"></i>
                    <span class="font-medium">Kategori</span>
                </a>
            </nav>

            <div class="p-4 mt-auto">
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-[#5d4037] flex items-center justify-center border border-white/10">
                            <i class="ph-bold ph-user-circle text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-none text-white">Admin Kasir</p>
                            <p class="text-[10px] text-gray-400">Online</p>
                        </div>
                    </div>
                    <button onclick="window.location.href='logout.php'" class="w-full py-2 text-[10px] font-bold uppercase tracking-widest text-[#d4a373] hover:text-white transition-all flex items-center justify-center gap-2">
                        <i class="ph ph-sign-out"></i> Logout
                    </button>
                </div>
            </div>
        </aside>

        <!-- Page Loader (Only for content area) -->
        <!-- Skeleton Page Loader -->
        <div id="loader" class="loader-wrapper !block p-8 bg-[#f8f9fa]">
            <div class="flex flex-col gap-8 w-full max-w-6xl mx-auto">
                <div class="skeleton-loader skeleton-header"></div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="skeleton-loader skeleton-card"></div>
                    <div class="skeleton-loader skeleton-card"></div>
                    <div class="skeleton-loader skeleton-card"></div>
                    <div class="skeleton-loader skeleton-card"></div>
                </div>
                <div class="card-gradient p-8 rounded-[2rem] h-64 border border-gray-100 flex flex-col gap-4">
                    <div class="skeleton-loader skeleton-text w-3/4"></div>
                    <div class="skeleton-loader skeleton-text w-1/2"></div>
                    <div class="skeleton-loader skeleton-text w-5/6"></div>
                    <div class="skeleton-loader skeleton-text w-2/3"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto min-w-0 relative">
            <!-- Top Header: WHITE -->
            <header class="h-20 border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 bg-white/80 backdrop-blur-md z-10 text-[#1a1a1a]">
                <div>
                    <h2 class="text-xl font-bold"><?php echo $title; ?></h2>
                    <p class="text-xs text-gray-500">Tarumpah Toha Tasik - POS System</p>
                </div>
                <div class="flex items-center gap-4">
                    <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center border border-gray-200 text-gray-500 hover:text-[#3e2723] transition-all shadow-sm">
                        <i class="ph ph-bell text-xl"></i>
                    </button>
                    <div class="h-10 w-[1px] bg-gray-200"></div>
                    <div class="text-right">
                        <p class="text-xs font-bold"><?php echo date('d M Y'); ?></p>
                        <p id="clock" class="text-[10px] text-[#3e2723] font-bold font-mono uppercase">12:00:00</p>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-8 fade-in-content">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

    <!-- Export Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <script>
        async function exportFullReportPDF(tableIds, titles, mainTitle, filename) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');
            
            doc.setFontSize(16);
            doc.text(mainTitle, 40, 40);
            doc.setFontSize(10);
            doc.text('Dicetak pada: ' + new Date().toLocaleString('id-ID'), 40, 60);
            
            let currentY = 90;
            
            for (let index = 0; index < tableIds.length; index++) {
                const tableId = tableIds[index];
                const table = document.getElementById(tableId);
                if (!table) continue;
                
                // Add Section Title
                doc.setFontSize(14);
                doc.setTextColor(62, 39, 35);
                doc.text(titles[index], 40, currentY);
                currentY += 10;
                
                const data = [];
                const headers = [];
                const rows = table.querySelectorAll('tr');
                let headerFound = false;

                rows.forEach((row, rowIndex) => {
                    const cols = row.querySelectorAll('th, td');
                    const rowData = [];
                    let isHeader = row.parentElement.tagName === 'THEAD' || (rowIndex === 0 && !headerFound);
                    
                    let currentCol = 0;
                    cols.forEach((col) => {
                        if (col.classList.contains('no-export')) return;
                        let text = col.innerText.trim();
                        let colspan = parseInt(col.getAttribute('colspan')) || 1;
                        
                        if (isHeader && !headerFound) {
                            headers.push(text);
                            for(let i=1; i<colspan; i++) headers.push("");
                        } else if (!isHeader) {
                            rowData[currentCol] = text;
                            for(let i=1; i<colspan; i++) { currentCol++; rowData[currentCol] = ""; }
                        }
                        currentCol++;
                    });
                    
                    if (isHeader) headerFound = true;
                    if (!isHeader && rowData.length > 0) {
                        while(rowData.length < headers.length) rowData.push("");
                        data.push(rowData);
                    }
                });

                doc.autoTable({
                    head: [headers],
                    body: data,
                    startY: currentY + 10,
                    theme: 'grid',
                    headStyles: { fillColor: [62, 39, 35] },
                    styles: { fontSize: 8 },
                    didParseCell: function(d) {
                        if (d.cell.text[0] && d.cell.text[0].toLowerCase().includes('total')) {
                            d.cell.styles.fontStyle = 'bold';
                        }
                    }
                });
                
                currentY = doc.lastAutoTable.finalY + 40;
                if(currentY > 750 && index < tableIds.length - 1) {
                    doc.addPage();
                    currentY = 50;
                }
            }
            doc.save(filename + '.pdf');
        }

        function exportFullReportExcel(tableIds, sectionTitles, filename) {
            const wb = XLSX.utils.book_new();
            const ws_data = [
                [filename.replace(/_/g, ' ')],
                ['Dicetak pada: ' + new Date().toLocaleString('id-ID')],
                []
            ];

            tableIds.forEach((id, index) => {
                const table = document.getElementById(id);
                if (table) {
                    // Add Section Title
                    ws_data.push([sectionTitles[index].toUpperCase()]);
                    
                    const rows = table.querySelectorAll('tr');
                    rows.forEach(row => {
                        const rowData = [];
                        const cols = row.querySelectorAll('th, td');
                        cols.forEach(col => {
                            if (!col.classList.contains('no-export')) {
                                rowData.push(col.innerText.trim());
                                // Handle colspan by padding with empty strings
                                let colspan = parseInt(col.getAttribute('colspan')) || 1;
                                for(let i=1; i<colspan; i++) rowData.push("");
                            }
                        });
                        if (rowData.length > 0) ws_data.push(rowData);
                    });
                    ws_data.push([]); // Spacer row
                    ws_data.push([]); // Double spacer
                }
            });

            const ws = XLSX.utils.aoa_to_sheet(ws_data);
            XLSX.utils.book_append_sheet(wb, ws, "Laporan_Lengkap");
            XLSX.writeFile(wb, filename + '.xlsx');
        }

        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('clock');
            if (clockEl) {
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Global Export Functions
        function exportTableToExcel(tableId, filename = 'laporan_tarumpah') {
            const table = document.getElementById(tableId);
            if (!table) return alert('Tabel tidak ditemukan!');
            
            // Clone table to remove items marked as no-export
            const clone = table.cloneNode(true);
            clone.querySelectorAll('.no-export').forEach(el => el.remove());
            
            const wb = XLSX.utils.table_to_book(clone, { sheet: "Laporan" });
            XLSX.writeFile(wb, filename + '.xlsx');
        }

        async function exportTableToPDF(tableId, title = 'Laporan Tarumpah', filename = 'laporan_tarumpah') {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');
            const table = document.getElementById(tableId);
            if (!table) return alert('Tabel tidak ditemukan!');

            doc.setFontSize(18);
            doc.text(title, 40, 40);
            doc.setFontSize(10);
            const dateStr = new Date().toLocaleString('id-ID');
            doc.text('Dicetak pada: ' + dateStr, 40, 60);

            // Extract data manually to filter columns correctly
            const data = [];
            const headers = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach((row, rowIndex) => {
                const cols = row.querySelectorAll('th, td');
                const rowData = [];
                let skipIndices = [];
                
                let isHeader = rowIndex === 0 || row.parentElement.tagName === 'THEAD';

                let currentCol = 0;
                cols.forEach((col) => {
                    // Skip if it's explicitly marked as no-export
                    if (col.classList.contains('no-export')) return;

                    let text = col.innerText.trim();
                    let colspan = parseInt(col.getAttribute('colspan')) || 1;
                    
                    if (isHeader && rowIndex === 0) {
                        headers.push(text);
                        for(let i=1; i<colspan; i++) headers.push("");
                    } else {
                        // Place text in the current available column
                        rowData[currentCol] = text;
                        // If there's a colspan, leave subsequent slots empty
                        for(let i=1; i<colspan; i++) {
                            currentCol++;
                            rowData[currentCol] = "";
                        }
                    }
                    currentCol++;
                });

                if (!isHeader && rowData.length > 0) {
                    // Ensure rowData has the same length as headers by padding with empty strings
                    while(rowData.length < headers.length) rowData.push("");
                    data.push(rowData);
                }
            });

            doc.autoTable({
                head: [headers],
                body: data,
                startY: 80,
                theme: 'grid',
                headStyles: { fillColor: [62, 39, 35] },
                styles: { fontSize: 8 },
                didParseCell: function(data) {
                    if (data.cell.text[0] && data.cell.text[0].toLowerCase().includes('total')) {
                        data.cell.styles.fontStyle = 'bold';
                    }
                }
            });

            doc.save(filename + '.pdf');
        }

        // Hide Loader on Page Load
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('loader');
            if (loader) {
                setTimeout(() => {
                    loader.classList.add('hide');
                }, 0); 
            }
        });
    </script>
</body>
</html>
<?php
}
?>
