<?php
// views/supplier.php

// Handle Add Supplier
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_supplier') {
    $nama = escape($_POST['nama_supplier']);
    $no_hp = escape($_POST['no_hp']);
    $alamat = escape($_POST['alamat']);

    $q = "INSERT INTO supplier (nama_supplier, no_hp, alamat) VALUES ('$nama', '$no_hp', '$alamat')";
    if (query($q)) {
        $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Supplier berhasil ditambahkan!</div>";
    }
}

$all_suppliers = query("SELECT * FROM supplier ORDER BY created_at DESC");
?>

<div class="space-y-8">
    <?php echo $message; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Add Supplier -->
        <div class="card-gradient rounded-3xl p-6 h-fit sticky top-28">
            <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                <i class="ph ph-plus-circle text-[#3e2723]"></i> Tambah Supplier Baru
            </h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_supplier">
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Nama Supplier</label>
                    <input type="text" name="nama_supplier" required class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">No. HP / Kontak</label>
                    <input type="text" name="no_hp" required class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Alamat</label>
                    <textarea name="alamat" rows="3" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]"></textarea>
                </div>
                <button type="submit" class="w-full btn-primary py-4 rounded-xl font-bold uppercase tracking-widest text-xs mt-2">Simpan Supplier</button>
            </form>
        </div>

        <!-- Supplier List -->
        <div class="lg:col-span-2 card-gradient rounded-3xl p-6">
            <h3 class="text-lg font-bold mb-6">Daftar Supplier</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] text-gray-400 uppercase tracking-widest border-b border-gray-100">
                            <th class="pb-4">Nama Supplier</th>
                            <th class="pb-4">No. HP</th>
                            <th class="pb-4">Alamat</th>
                            <th class="pb-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if(empty($all_suppliers)): ?>
                            <tr><td colspan="4" class="py-10 text-center text-gray-400">Belum ada supplier.</td></tr>
                        <?php else: foreach($all_suppliers as $s): ?>
                        <tr class="border-b border-gray-50 group hover:bg-gray-50 transition-colors">
                            <td class="py-4 font-bold text-[#1a1a1a]"><?php echo $s['nama_supplier']; ?></td>
                            <td class="py-4 text-gray-600"><?php echo $s['no_hp']; ?></td>
                            <td class="py-4 text-gray-400 text-xs italic"><?php echo $s['alamat']; ?></td>
                            <td class="py-4 text-right">
                                <button class="p-2 text-gray-400 hover:text-[#3e2723]"><i class="ph ph-pencil-simple text-lg"></i></button>
                                <button class="p-2 text-gray-400 hover:text-rose-500"><i class="ph ph-trash text-lg"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
