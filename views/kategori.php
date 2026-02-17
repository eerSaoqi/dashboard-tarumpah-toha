<?php
// views/kategori.php
$message = "";

// Handle DELETE Kategori
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    // Cek apakah ada produk yang pakai kategori ini
    $cek_produk = countRows("produk_tarumpah", "WHERE id_kategori = $id_del");
    
    if ($cek_produk > 0) {
        $message = "<div class='bg-amber-500/10 border border-amber-500 text-amber-500 p-4 rounded-2xl mb-6 text-sm font-bold'>⚠ Gagal Hapus! Ada $cek_produk produk yang menggunakan kategori ini. Silakan pindahkan atau hapus produk terlebih dahulu.</div>";
    } else {
        query("DELETE FROM kategori WHERE id_kategori = $id_del");
        $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Kategori berhasil dihapus permanen.</div>";
    }
}

// Handle Add/Edit Kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $nama = escape($_POST['nama_kategori']);
    $desc = escape($_POST['deskripsi']);

    if ($_POST['action'] == 'add_kategori') {
        query("INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama', '$desc')");
        $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Kategori $nama berhasil ditambahkan!</div>";
    } elseif ($_POST['action'] == 'edit_kategori') {
        $id_edit = (int)$_POST['id_kategori'];
        query("UPDATE kategori SET nama_kategori = '$nama', deskripsi = '$desc' WHERE id_kategori = $id_edit");
        $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Kategori $nama berhasil diperbarui!</div>";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $res = query("SELECT * FROM kategori WHERE id_kategori = $id_edit");
    if (!empty($res)) $edit_data = $res[0];
}
$all_categories = query("SELECT * FROM kategori ORDER BY id_kategori DESC");
?>

<div class="space-y-8">
    <?php echo $message; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Add/Edit Kategori -->
        <div class="card-gradient rounded-3xl p-6 h-fit sticky top-28">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="ph ph-<?php echo $edit_data ? 'pencil-simple' : 'plus-circle'; ?> text-[#3e2723]"></i> 
                    <?php echo $edit_data ? 'Edit Kategori' : 'Tambah Kategori'; ?>
                </h3>
                <?php if($edit_data): ?>
                    <a href="?page=kategori" class="text-[10px] font-bold text-rose-500 hover:underline">BATAL</a>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit_kategori' : 'add_kategori'; ?>">
                <?php if($edit_data): ?>
                    <input type="hidden" name="id_kategori" value="<?php echo $edit_data['id_kategori']; ?>">
                <?php endif; ?>

                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Nama Kategori</label>
                    <input type="text" name="nama_kategori" required value="<?php echo $edit_data ? $edit_data['nama_kategori'] : ''; ?>" placeholder="Contoh: Koleksi Premium" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]" placeholder="Jelaskan kategori ini..."><?php echo $edit_data ? $edit_data['deskripsi'] : ''; ?></textarea>
                </div>
                <button type="submit" class="w-full btn-primary py-4 rounded-xl font-bold uppercase tracking-widest text-xs mt-2 shadow-lg">
                    <?php echo $edit_data ? 'UPDATE KATEGORI' : 'SIMPAN KATEGORI'; ?>
                </button>
            </form>
        </div>

        <!-- Kategori List -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Search Bar -->
            <div class="relative w-full max-w-sm">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="search_kategori" placeholder="Cari kategori..." class="w-full bg-white border border-gray-200 rounded-2xl py-3 pl-12 pr-4 text-xs focus:outline-none focus:border-[#3e2723] transition-all text-[#1a1a1a] shadow-sm">
            </div>

            <div class="card-gradient rounded-3xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold uppercase tracking-tighter text-[#3e2723]">Daftar Kategori</h3>
                    <span class="text-[10px] font-bold text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-100"><?php echo count($all_categories); ?> ITEM</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                <th class="pb-4">Informasi Kategori</th>
                                <th class="pb-4">Deskripsi</th>
                                <th class="pb-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                        <?php if(empty($all_categories)): ?>
                            <tr><td colspan="3" class="py-10 text-center text-gray-400">Belum ada kategori.</td></tr>
                        <?php else: foreach($all_categories as $k): ?>
                        <tr class="border-b border-gray-50 group hover:bg-gray-50 transition-colors">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#3e2723]/5 flex items-center justify-center text-[#3e2723]">
                                        <i class="ph-bold ph-tag"></i>
                                    </div>
                                    <p class="font-bold text-[#1a1a1a] uppercase text-xs"><?php echo $k['nama_kategori']; ?></p>
                                </div>
                            </td>
                            <td class="py-4 text-gray-500 text-[11px] leading-relaxed max-w-[300px]"><?php echo $k['deskripsi'] ? $k['deskripsi'] : '<span class="italic text-gray-300">Tidak ada deskripsi</span>'; ?></td>
                            <td class="py-4 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="?page=kategori&edit=<?php echo $k['id_kategori']; ?>" title="Edit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                    </a>
                                    <a href="?page=kategori&delete=<?php echo $k['id_kategori']; ?>" onclick="return confirm('Hapus kategori ini?')" title="Hapus" class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 text-rose-500 hover:bg-rose-100 transition-colors">
                                        <i class="ph ph-trash text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Live Search for Kategori
document.getElementById('search_kategori').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const catName = row.querySelector('p')?.innerText.toLowerCase() || '';
        const catDesc = row.cells[1]?.innerText.toLowerCase() || '';
        
        if (catName.includes(term) || catDesc.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
