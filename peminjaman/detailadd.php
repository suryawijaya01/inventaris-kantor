<?php
$pageTitle = 'Tambah Item Peminjaman';
$breadcrumbs = [
    ['title' => 'Peminjaman', 'url' => 'index.php'],
    ['title' => 'Tambah Item', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

$db = Database::getInstance()->getConnection();
$error = '';

// Get peminjaman ID
$peminjaman_id = isset($_GET['peminjaman_id']) ? (int)$_GET['peminjaman_id'] : 0;

// Verify peminjaman exists and belongs to user
$checkQuery = "SELECT * FROM peminjaman WHERE id = $peminjaman_id AND user_id = {$user['id']} AND status = 'diajukan'";
$checkResult = $db->query($checkQuery);

if (!$checkResult || $checkResult->num_rows === 0) {
    header('Location: index.php?error=' . urlencode('Peminjaman tidak ditemukan atau tidak dapat diubah'));
    exit();
}

// Get available inventories (excluding already borrowed items in this peminjaman)
$inventoryQuery = "SELECT i.*, k.nama_kategori 
                   FROM inventaris i 
                   JOIN kategori k ON i.kategori_id = k.id 
                   WHERE i.jumlah_tersedia > 0 
                   AND i.id NOT IN (
                       SELECT inventaris_id FROM peminjaman_detail WHERE peminjaman_id = $peminjaman_id
                   )
                   ORDER BY i.nama_inventaris ASC";
$inventoryResult = $db->query($inventoryQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventaris_id = (int)$_POST['inventaris_id'];
    $jumlah = (int)$_POST['jumlah'];
    
    // Check stock
    $stockQuery = "SELECT jumlah_tersedia, nama_inventaris FROM inventaris WHERE id = $inventaris_id";
    $stockResult = $db->query($stockQuery);
    $stockData = $stockResult->fetch_assoc();
    
    if ($jumlah > $stockData['jumlah_tersedia']) {
        $error = "Stok {$stockData['nama_inventaris']} tidak mencukupi (tersedia: {$stockData['jumlah_tersedia']})";
    } else {
        // Insert detail
        $insertQuery = "INSERT INTO peminjaman_detail (peminjaman_id, inventaris_id, jumlah) 
                        VALUES ($peminjaman_id, $inventaris_id, $jumlah)";
        
        if ($db->query($insertQuery)) {
            // Update stock
            $updateQuery = "UPDATE inventaris SET jumlah_tersedia = jumlah_tersedia - $jumlah WHERE id = $inventaris_id";
            $db->query($updateQuery);
            
            header('Location: detail.php?id=' . $peminjaman_id . '&success=' . urlencode('Item berhasil ditambahkan'));
            exit();
        } else {
            $error = 'Gagal menambahkan item';
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
            <h5><i class="fas fa-plus"></i> Tambah Item Peminjaman</h5>
        </div>
        <div class="card-body">
            <?php if ($inventoryResult && $inventoryResult->num_rows > 0): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Inventaris <span style="color: red;">*</span></label>
                        <select name="inventaris_id" class="form-control" required style="padding: 12px 15px;">
                            <option value="">-- Pilih Inventaris --</option>
                            <?php while ($inv = $inventoryResult->fetch_assoc()): ?>
                                <option value="<?php echo $inv['id']; ?>">
                                    <?php echo htmlspecialchars($inv['nama_inventaris']); ?> 
                                    (Stok: <?php echo $inv['jumlah_tersedia']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah <span style="color: red;">*</span></label>
                        <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="detail.php?id=<?php echo $peminjaman_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    Tidak ada inventaris yang tersedia atau semua item sudah ditambahkan ke peminjaman ini.
                </div>
                <a href="detail.php?id=<?php echo $peminjaman_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>