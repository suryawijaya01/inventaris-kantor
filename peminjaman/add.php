<?php
$pageTitle = 'Tambah Peminjaman';
$breadcrumbs = [
    ['title' => 'Peminjaman', 'url' => 'index.php'],
    ['title' => 'Tambah Peminjaman', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

$db = Database::getInstance()->getConnection();
$error = '';

// Get available inventories
$inventoryQuery = "SELECT i.*, k.nama_kategori 
                   FROM inventaris i 
                   JOIN kategori k ON i.kategori_id = k.id 
                   WHERE i.jumlah_tersedia > 0 
                   ORDER BY i.nama_inventaris ASC";
$inventoryResult = $db->query($inventoryQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_peminjaman = $func->generateNomorPeminjaman();
    $user_id = $user['id'];
    $tanggal_pinjam = $db->real_escape_string($_POST['tanggal_pinjam']);
    $tanggal_kembali_rencana = $db->real_escape_string($_POST['tanggal_kembali_rencana']);
    $catatan = $db->real_escape_string(trim($_POST['catatan']));
    $inventaris_items = isset($_POST['inventaris_id']) ? $_POST['inventaris_id'] : [];
    $jumlah_items = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];
    
    // Validation
    if (empty($inventaris_items)) {
        $error = 'Pilih minimal satu inventaris';
    } elseif (strtotime($tanggal_kembali_rencana) <= strtotime($tanggal_pinjam)) {
        $error = 'Tanggal kembali harus lebih besar dari tanggal pinjam';
    } else {
        // Check stock availability
        $stockError = false;
        foreach ($inventaris_items as $index => $inv_id) {
            $jumlah = (int)$jumlah_items[$index];
            $checkQuery = "SELECT jumlah_tersedia, nama_inventaris FROM inventaris WHERE id = $inv_id";
            $checkResult = $db->query($checkQuery);
            $checkData = $checkResult->fetch_assoc();
            
            if ($jumlah > $checkData['jumlah_tersedia']) {
                $error = "Stok {$checkData['nama_inventaris']} tidak mencukupi (tersedia: {$checkData['jumlah_tersedia']})";
                $stockError = true;
                break;
            }
        }
        
        if (!$stockError) {
            // Insert peminjaman
            $query = "INSERT INTO peminjaman (nomor_peminjaman, user_id, tanggal_pinjam, tanggal_kembali_rencana, status, catatan) 
                      VALUES ('$nomor_peminjaman', $user_id, '$tanggal_pinjam', '$tanggal_kembali_rencana', 'diajukan', '$catatan')";
            
            if ($db->query($query)) {
                $peminjaman_id = $db->insert_id;
                
                // Insert detail and update stock
                foreach ($inventaris_items as $index => $inv_id) {
                    $jumlah = (int)$jumlah_items[$index];
                    
                    // Insert detail
                    $detailQuery = "INSERT INTO peminjaman_detail (peminjaman_id, inventaris_id, jumlah) 
                                    VALUES ($peminjaman_id, $inv_id, $jumlah)";
                    $db->query($detailQuery);
                    
                    // Update stock
                    $updateQuery = "UPDATE inventaris SET jumlah_tersedia = jumlah_tersedia - $jumlah WHERE id = $inv_id";
                    $db->query($updateQuery);
                }
                
                header('Location: index.php?success=' . urlencode('Peminjaman berhasil diajukan'));
                exit();
            } else {
                $error = 'Gagal menambahkan peminjaman';
            }
        }
    }
}
?>

<?php include __DIR__ . '/../views/sidebar.php'; ?>
<?php include __DIR__ . '/../views/topnav.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../views/breadcrumb.php'; ?>
    
    <?php if ($error): ?>
        <?php echo $func->getAlert('danger', $error); ?>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-plus"></i> Tambah Peminjaman</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Tanggal Pinjam <span style="color: red;">*</span></label>
                    <input type="date" name="tanggal_pinjam" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Kembali Rencana <span style="color: red;">*</span></label>
                    <input type="date" name="tanggal_kembali_rencana" class="form-control" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="catatan" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Inventaris <span style="color: red;">*</span></label>
                    <div id="inventaris-container">
                        <div class="inventaris-item" style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <select name="inventaris_id[]" class="form-control" required style="padding: 12px 15px; flex: 2;">
                                <option value="">-- Pilih Inventaris --</option>
                                <?php 
                                $inventoryResult->data_seek(0);
                                while ($inv = $inventoryResult->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $inv['id']; ?>">
                                        <?php echo htmlspecialchars($inv['nama_inventaris']); ?> 
                                        (Stok: <?php echo $inv['jumlah_tersedia']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" name="jumlah[]" class="form-control" min="1" value="1" required style="flex: 1;">
                            <button type="button" class="btn btn-danger" onclick="removeItem(this)" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" onclick="addInventarisItem()">
                        <i class="fas fa-plus"></i> Tambah Item
                    </button>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function addInventarisItem() {
        const container = document.getElementById('inventaris-container');
        const item = container.querySelector('.inventaris-item').cloneNode(true);
        
        // Reset values
        item.querySelector('select').value = '';
        item.querySelector('input[type="number"]').value = '1';
        item.querySelector('button').style.display = 'inline-flex';
        
        container.appendChild(item);
        updateRemoveButtons();
    }
    
    function removeItem(btn) {
        btn.closest('.inventaris-item').remove();
        updateRemoveButtons();
    }
    
    function updateRemoveButtons() {
        const items = document.querySelectorAll('.inventaris-item');
        items.forEach((item, index) => {
            const btn = item.querySelector('button');
            if (items.length > 1) {
                btn.style.display = 'inline-flex';
            } else {
                btn.style.display = 'none';
            }
        });
    }
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>