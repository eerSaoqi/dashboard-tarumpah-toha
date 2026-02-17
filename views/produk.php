<?php
// views/produk.php
$categories = query("SELECT * FROM kategori");
$all_sizes = query("SELECT * FROM ukuran ORDER BY ukuran ASC");

// Handle DELETE Product
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $cek_trx = countRows("detail_transaksi", "WHERE id_produk = $id_del");
    
    if ($cek_trx > 0) {
        echo "<script>alert('Gagal Hapus! Produk ini sudah memiliki riwayat transaksi. Silakan gunakan fitur NONAKTIFKAN saja agar data laporan tetap aman.'); window.location.href='?page=produk';</script>";
    } else {
        // Hapus Foto dari Folder
        $prod = query("SELECT foto FROM produk_tarumpah WHERE id_produk = $id_del");
        if (!empty($prod)) {
            $foto_to_delete = $prod[0]['foto'];
            if ($foto_to_delete != 'default.jpg' && file_exists("assets/images/produk/" . $foto_to_delete)) {
                unlink("assets/images/produk/" . $foto_to_delete);
            }
        }

        query("DELETE FROM stok_produk WHERE id_produk = $id_del");
        query("DELETE FROM produk_tarumpah WHERE id_produk = $id_del");
        echo "<script>alert('Produk berhasil dihapus permanen beserta fotonya.'); window.location.href='?page=produk';</script>";
    }
}

// Handle TOGGLE STATUS Product
if (isset($_GET['toggle_status'])) {
    $id_toggle = (int)$_GET['toggle_status'];
    $res = query("SELECT status FROM produk_tarumpah WHERE id_produk = $id_toggle");
    if (!empty($res)) {
        $new_status = ($res[0]['status'] == 'aktif') ? 'nonaktif' : 'aktif';
        query("UPDATE produk_tarumpah SET status = '$new_status' WHERE id_produk = $id_toggle");
    }
    echo "<script>window.location.href='?page=produk';</script>";
}

// Handle Add/Edit Product
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_new_size') {
        $ukuran_baru = escape($_POST['ukuran_baru']);
        if (!empty($ukuran_baru)) {
            // Cek jika ukuran sudah ada
            $cek = query("SELECT id_ukuran FROM ukuran WHERE ukuran = '$ukuran_baru'");
            if (empty($cek)) {
                query("INSERT INTO ukuran (ukuran) VALUES ('$ukuran_baru')");
                $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Ukuran $ukuran_baru Berhasil Ditambahkan!</div>";
                // Refresh list ukuran
                $all_sizes = query("SELECT * FROM ukuran ORDER BY ukuran ASC");
            } else {
                $message = "<div class='bg-amber-500/10 border border-amber-500 text-amber-500 p-4 rounded-2xl mb-6 text-sm font-bold'>⚠ Ukuran $ukuran_baru sudah ada!</div>";
            }
        }
    }
    
    // Extract product data for add/edit actions
    if ($_POST['action'] == 'add_produk' || $_POST['action'] == 'edit_produk') {
        $nama = escape($_POST['nama_produk']);
        $harga = (float)$_POST['harga'];
        $id_kategoris = isset($_POST['id_kategoris']) ? $_POST['id_kategoris'] : [];
        $desc = escape($_POST['deskripsi']);
        $selected_sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];
        $stocks = isset($_POST['stocks']) ? $_POST['stocks'] : []; // Array [id_ukuran => qty]
    }

    if ($_POST['action'] == 'add_produk') {
        // Handle Foto
        $foto_name = "default.jpg";
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "assets/images/produk/";
            $file_ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
            $foto_name = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "", $nama) . "." . $file_ext;
            move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $foto_name);
        }

        $q = "INSERT INTO produk_tarumpah (nama_produk, harga, deskripsi, foto) VALUES ('$nama', $harga, '$desc', '$foto_name')";
        if (query($q)) {
            $id_produk = lastInsertId();
            
            // Simpan Banyak Kategori
            if (!empty($id_kategoris)) {
                foreach ($id_kategoris as $id_kat) {
                    $id_kat = (int)$id_kat;
                    query("INSERT INTO produk_kategori (id_produk, id_kategori) VALUES ($id_produk, $id_kat)");
                }
            }

            foreach ($selected_sizes as $id_size) {
                $id_size_int = (int)$id_size;
                // Stok awal selalu 0 agar tercatat lewat menu Pembelian
                query("INSERT INTO stok_produk (id_produk, id_ukuran, stok) VALUES ($id_produk, $id_size_int, 0)");
            }
            $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Produk Berhasil Ditambah!</div>";
        }
    } elseif ($_POST['action'] == 'edit_produk') {
        $id_produk = (int)$_POST['id_produk'];
        $old_foto = $_POST['old_foto'];
        $foto_name = $old_foto;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "assets/images/produk/";
            $file_ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
            $foto_name = time() . "_edit_" . preg_replace("/[^a-zA-Z0-9]/", "", $nama) . "." . $file_ext;
            
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $foto_name)) {
                // Hapus foto lama jika bukan default
                if ($old_foto != 'default.jpg' && file_exists($target_dir . $old_foto)) {
                    unlink($target_dir . $old_foto);
                }
            }
        }

        $q = "UPDATE produk_tarumpah SET nama_produk='$nama', harga=$harga, deskripsi='$desc', foto='$foto_name' WHERE id_produk=$id_produk";
        if (query($q)) {
            // Update Kategori (Hapus lama, isi baru)
            query("DELETE FROM produk_kategori WHERE id_produk = $id_produk");
            if (!empty($id_kategoris)) {
                foreach ($id_kategoris as $id_kat) {
                    $id_kat = (int)$id_kat;
                    query("INSERT INTO produk_kategori (id_produk, id_kategori) VALUES ($id_produk, $id_kat)");
                }
            }

            foreach ($selected_sizes as $id_size) {
                $id_size = (int)$id_size;
                $check = query("SELECT id_stok FROM stok_produk WHERE id_produk=$id_produk AND id_ukuran=$id_size");
                if (empty($check)) {
                    // Jika ada ukuran baru yang dicentang saat edit, stok awalnya 0
                    query("INSERT INTO stok_produk (id_produk, id_ukuran, stok) VALUES ($id_produk, $id_size, 0)");
                }
            }
            $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Produk Berhasil Diperbarui!</div>";
        }
    }
}

$edit_data = null;
$edit_kat_ids = [];
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res = query("SELECT * FROM produk_tarumpah WHERE id_produk = $id_edit");
    if (!empty($res)) {
        $edit_data = $res[0];
        
        // Ambil Kategori Terpilih
        $kat_res = query("SELECT id_kategori FROM produk_kategori WHERE id_produk = $id_edit");
        foreach($kat_res as $kr) { $edit_kat_ids[] = $kr['id_kategori']; }

        $edit_sizes_res = query("SELECT id_ukuran FROM stok_produk WHERE id_produk = $id_edit");
        $edit_sizes = [];
        foreach($edit_sizes_res as $es) {
            $edit_sizes[] = $es['id_ukuran'];
        }
    }
}

$selected_kat = isset($_GET['kat']) ? $_GET['kat'] : '';

$base_query = "SELECT p.*, GROUP_CONCAT(k.nama_kategori SEPARATOR ', ') as nama_kategori 
               FROM produk_tarumpah p 
               LEFT JOIN produk_kategori pk ON p.id_produk = pk.id_produk
               LEFT JOIN kategori k ON pk.id_kategori = k.id_kategori";

if ($selected_kat) {
    $base_query .= " WHERE p.id_produk IN (SELECT id_produk FROM produk_kategori WHERE id_kategori = " . (int)$selected_kat . ")";
}
$base_query .= " GROUP BY p.id_produk ORDER BY p.status ASC, p.created_at DESC";
$all_products = query($base_query);
?>

<div class="space-y-8">
    <?php echo $message; ?>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- List -->
    <div class="flex-1 w-full space-y-6 order-2 lg:order-1">
        <!-- Search & Filter Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full max-w-sm">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="search_produk_db" placeholder="Cari di database..." class="w-full bg-white border border-gray-200 rounded-2xl py-3 pl-12 pr-4 text-xs focus:outline-none focus:border-[#3e2723] transition-all text-[#1a1a1a] shadow-sm">
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 no-scrollbar">
                <a href="?page=produk" class="px-4 py-2 rounded-xl text-[10px] font-bold whitespace-nowrap transition-all <?php echo !$selected_kat ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
                    Semua
                </a>
                <?php foreach($categories as $kat): ?>
                <a href="?page=produk&kat=<?php echo $kat['id_kategori']; ?>" class="px-4 py-2 rounded-xl text-[10px] font-bold whitespace-nowrap transition-all <?php echo $selected_kat == $kat['id_kategori'] ? 'bg-[#3e2723] text-white shadow-md' : 'bg-white text-gray-400 border border-gray-100' ?>">
                    <?php echo $kat['nama_kategori']; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-gradient rounded-3xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold uppercase tracking-tighter text-[#3e2723]">Database Produk</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-1"><?php echo count($all_products); ?> UNIT TERDAFTAR</p>
                </div>
                <div class="flex gap-2 no-print">
                    <button onclick="exportTableToExcel('table_produk', 'Data_Produk_Tarumpah')" class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-bold border border-emerald-100 hover:bg-emerald-100 transition-all flex items-center gap-2">
                        <i class="ph ph-file-xls text-base"></i> EXCEL
                    </button>
                    <button onclick="exportTableToPDF('table_produk', 'Data Produk Tarumpah Toha', 'Data_Produk')" class="px-4 py-2 bg-rose-50 text-rose-600 rounded-xl text-[10px] font-bold border border-rose-100 hover:bg-rose-100 transition-all flex items-center gap-2">
                        <i class="ph ph-file-pdf text-base"></i> PDF
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
            <table id="table_produk" class="w-full text-left">
                <thead>
                    <tr class="text-[10px] text-gray-400 uppercase tracking-widest border-b border-gray-100">
                        <th class="pb-4">Model Tarumpah</th>
                        <th class="pb-4">Varian & Stok Detail</th>
                        <th class="pb-4">Harga</th>
                        <th class="pb-4 text-right no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach($all_products as $p): 
                        $pid = $p['id_produk'];
                        $prod_details = query("SELECT u.ukuran, s.stok FROM stok_produk s JOIN ukuran u ON s.id_ukuran = u.id_ukuran WHERE s.id_produk = $pid");
                        $foto_url = "assets/images/produk/" . $p['foto'];
                        if (!file_exists($foto_url) || empty($p['foto'])) $foto_url = "https://ui-avatars.com/api/?name=" . urlencode($p['nama_produk']) . "&background=3e2723&color=fff";
                    ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <img src="<?php echo $foto_url; ?>" class="w-10 h-10 rounded-lg object-cover <?php echo $p['status'] == 'nonaktif' ? 'grayscale opacity-50' : ''; ?>">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-[#1a1a1a] uppercase text-xs <?php echo $p['status'] == 'nonaktif' ? 'text-gray-400' : ''; ?>"><?php echo $p['nama_produk']; ?></p>
                                        <span class="px-1.5 py-0.5 rounded text-[7px] font-black uppercase <?php echo $p['status'] == 'aktif' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'; ?>">
                                            <?php echo $p['status']; ?>
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-medium"><?php echo $p['nama_kategori']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-[250px]">
                                    <?php foreach($prod_details as $sd): 
                                        $is_low_stock = ($sd['stok'] < 5);
                                    ?>
                                    <div class="flex flex-col bg-white border border-gray-100 rounded-md p-1 min-w-[45px] text-center shadow-sm <?php echo $is_low_stock ? 'bg-rose-50 border-rose-100' : ''; ?>">
                                        <span class="text-[8px] text-gray-400 uppercase font-black"><?php echo $sd['ukuran']; ?></span>
                                        <span class="text-[10px] font-bold <?php echo $is_low_stock ? 'text-rose-600' : 'text-[#3e2723]'; ?>"><?php echo $sd['stok']; ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                        </td>
                        <td class="py-4 font-bold text-[#3e2723]">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                        <td class="py-4 text-right no-print">
                            <div class="flex justify-end gap-1">
                                <a href="?page=produk&toggle_status=<?php echo $p['id_produk']; ?>" title="<?php echo $p['status'] == 'aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center <?php echo $p['status'] == 'aktif' ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'; ?>">
                                    <i class="ph ph-<?php echo $p['status'] == 'aktif' ? 'eye' : 'eye-slash'; ?> text-base"></i>
                                </a>
                                <a href="?page=produk&edit=<?php echo $p['id_produk']; ?>" title="Edit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100">
                                    <i class="ph ph-pencil-simple text-base"></i>
                                </a>
                                <a href="?page=produk&delete=<?php echo $p['id_produk']; ?>" onclick="return confirm('Hapus produk ini secara permanen?')" title="Hapus" class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 text-rose-500 hover:bg-rose-100">
                                    <i class="ph ph-trash text-base"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    
    <!-- Form Add/Edit (Sidebar) -->
    <div class="lg:w-[380px] flex-shrink-0 order-1 lg:order-2">
        <div class="card-gradient rounded-3xl p-6 sticky top-[100px] bg-white border-t-8 border-[#3e2723] shadow-2xl h-fit max-h-[calc(100vh-140px)] overflow-y-auto custom-scroll">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="ph ph-<?php echo $edit_data ? 'pencil-simple' : 'plus-circle'; ?> text-[#3e2723]"></i> 
                    <?php echo $edit_data ? 'Edit Produk' : 'Tambah Produk'; ?>
                </h3>
                <?php if($edit_data): ?>
                    <a href="?page=produk" class="text-[10px] font-bold text-rose-500 hover:underline">BATAL</a>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit_produk' : 'add_produk'; ?>">
                <?php if($edit_data): ?>
                    <input type="hidden" name="id_produk" value="<?php echo $edit_data['id_produk']; ?>">
                    <input type="hidden" name="old_foto" value="<?php echo $edit_data['foto']; ?>">
                <?php endif; ?>
                
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Foto Produk</label>
                    <div class="relative group">
                        <input type="file" name="foto" id="foto_input" class="hidden" accept="image/*" onchange="previewImage(this)">
                        <label for="foto_input" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-50 transition-all overflow-hidden relative">
                            <?php 
                            $prev_img = "";
                            if ($edit_data && !empty($edit_data['foto'])) {
                                $prev_img = "assets/images/produk/" . $edit_data['foto'];
                            }
                            ?>
                            <div id="preview_placeholder" class="flex flex-col items-center justify-center <?php echo $prev_img ? 'hidden' : ''; ?>">
                                <i class="ph ph-image text-3xl text-gray-300"></i>
                                <p class="text-[10px] text-gray-400 font-bold mt-2">UPLOAD FOTO</p>
                            </div>
                            <img id="img_preview" src="<?php echo $prev_img; ?>" class="absolute inset-0 w-full h-full object-cover <?php echo $prev_img ? '' : 'hidden'; ?>">
                        </label>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Nama Produk</label>
                    <input type="text" name="nama_produk" required value="<?php echo $edit_data ? $edit_data['nama_produk'] : ''; ?>" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]">
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Harga</label>
                        <input type="number" name="harga" required value="<?php echo $edit_data ? $edit_data['harga'] : ''; ?>" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Pilih Kategori <span class="text-[8px] text-gray-300 italic">(Bisa pilih banyak)</span></label>
                        <div class="grid grid-cols-2 gap-2 border border-gray-100 rounded-xl p-3 bg-gray-50/50 max-h-32 overflow-y-auto custom-scroll">
                            <?php foreach($categories as $c): 
                                $is_kat_checked = in_array($c['id_kategori'], $edit_kat_ids);
                            ?>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="id_kategoris[]" value="<?php echo $c['id_kategori']; ?>" <?php echo $is_kat_checked ? 'checked' : ''; ?> class="w-4 h-4 rounded border-gray-300 text-[#3e2723] focus:ring-[#3e2723]">
                                <span class="text-[10px] font-medium text-gray-600 group-hover:text-[#3e2723] transition-colors truncate"><?php echo $c['nama_kategori']; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Varian Ukuran Tersedia</label>
                    <div class="grid grid-cols-3 gap-2 max-h-80 overflow-y-auto p-2 custom-scroll bg-white/50 rounded-xl border border-gray-100">
                        <?php foreach($all_sizes as $s): 
                            $is_checked = ($edit_data && in_array($s['id_ukuran'], $edit_sizes));
                        ?>
                        <label class="flex items-center gap-2 p-2.5 bg-white rounded-lg border border-gray-50 shadow-sm cursor-pointer hover:bg-gray-50 transition-all group">
                            <input type="checkbox" name="sizes[]" value="<?php echo $s['id_ukuran']; ?>" class="w-4 h-4 rounded border-gray-300 text-[#3e2723] focus:ring-[#3e2723]" <?php echo $is_checked ? 'checked' : ''; ?>>
                            <span class="text-[10px] font-bold text-gray-700 group-hover:text-[#3e2723]">UK <?php echo $s['ukuran']; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Quick Add Size -->
                    <div class="mt-2 pt-3 border-t border-gray-100">
                        <button type="button" onclick="document.getElementById('quick_add_size_box').classList.toggle('hidden')" class="text-[9px] font-black text-[#3e2723] hover:text-[#5d4037] flex items-center gap-1.5 tracking-wider uppercase">
                            <i class="ph-bold ph-plus-circle"></i> Tambah Ukuran Baru
                        </button>
                        <div id="quick_add_size_box" class="hidden mt-3 p-3 bg-gray-50 rounded-2xl border border-gray-100 animate-fade-in">
                            <div class="flex gap-2">
                                <input type="text" id="new_size_input" placeholder="No. Ukuran" class="flex-1 bg-white border border-gray-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-[#3e2723]">
                                <button type="button" onclick="submitNewSize()" class="bg-[#3e2723] text-white px-4 py-2 rounded-xl text-[10px] font-bold shadow-md hover:bg-[#5d4037] transition-all">TAMBAH</button>
                            </div>
                            <p class="text-[8px] text-gray-400 mt-2 italic font-medium">*Ukuran yang ditambah akan muncul di daftar pilihan di atas.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="2" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]"><?php echo $edit_data ? $edit_data['deskripsi'] : ''; ?></textarea>
                </div>

                <button type="submit" class="w-full btn-primary py-4 rounded-xl font-bold uppercase tracking-widest text-xs mt-2 shadow-lg">
                    <?php echo $edit_data ? 'UPDATE PRODUK' : 'SIMPAN PRODUK'; ?>
                </button>
            </form>
        </div>
    </div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('img_preview').src = e.target.result;
            document.getElementById('img_preview').classList.remove('hidden');
            document.getElementById('preview_placeholder').classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleStockInput(checkbox, sizeId) {
    const container = document.getElementById('stock_container_' + sizeId);
    if (checkbox.checked) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

function submitNewSize() {
    const val = document.getElementById('new_size_input').value;
    if (!val) return alert('Masukkan nomor ukuran!');
    
    // Gunakan form dummy untuk submit tanpa merusak input produk yang sedang diisi
    const form = document.createElement('form');
    form.method = 'POST';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'add_new_size';
    
    const sizeInput = document.createElement('input');
    sizeInput.type = 'hidden';
    sizeInput.name = 'ukuran_baru';
    sizeInput.value = val;
    
    form.appendChild(actionInput);
    form.appendChild(sizeInput);
    document.body.appendChild(form);
    form.submit();
}

// Live Search for Product Database Table
document.getElementById('search_produk_db').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const productName = row.querySelector('p.font-bold').innerText.toLowerCase();
        const categoryName = row.querySelector('p.text-gray-400').innerText.toLowerCase();
        
        if (productName.includes(term) || categoryName.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-thumb { background: #3e272320; border-radius: 10px; }
</style>

<style>
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: #3e272344; border-radius: 10px; }
</style>
