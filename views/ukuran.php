<?php
// views/ukuran.php

// Handle Add Size
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_ukuran') {
    $uk = escape($_POST['ukuran']);

    $q = "INSERT INTO ukuran (ukuran) VALUES ('$uk')";
    if (query($q)) {
        $message = "<div class='bg-emerald-500/10 border border-emerald-500 text-emerald-500 p-4 rounded-2xl mb-6 text-sm font-bold'>✓ Ukuran berhasil ditambahkan!</div>";
    }
}

$all_sizes = query("SELECT * FROM ukuran ORDER BY ukuran ASC");
?>

<div class="max-w-2xl mx-auto space-y-8">
    <?php echo $message; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Form Add Size -->
        <div class="card-gradient rounded-3xl p-6 h-fit">
            <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                <i class="ph ph-plus-circle text-[#3e2723]"></i> Tambah Ukuran
            </h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_ukuran">
                <div class="space-y-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold tracking-widest pl-2">Nilai Ukuran (e.g. 38, 39, XL)</label>
                    <input type="text" name="ukuran" required placeholder="Contoh: 42" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-[#3e2723]">
                </div>
                <button type="submit" class="w-full btn-primary py-4 rounded-xl font-bold uppercase tracking-widest text-xs mt-2">Simpan Ukuran</button>
            </form>
        </div>

        <!-- Size List -->
        <div class="card-gradient rounded-3xl p-6">
            <h3 class="text-lg font-bold mb-6">Daftar Ukuran</h3>
            <div class="grid grid-cols-2 gap-3">
                <?php if(empty($all_sizes)): ?>
                    <p class="col-span-full text-center text-gray-400 py-4 italic">Belum ada data.</p>
                <?php else: foreach($all_sizes as $s): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-100 rounded-xl group">
                    <span class="font-bold text-[#1a1a1a]"><?php echo $s['ukuran']; ?></span>
                    <button class="text-gray-300 hover:text-rose-500 transition-colors"><i class="ph ph-trash"></i></button>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
