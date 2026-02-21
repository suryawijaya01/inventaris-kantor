<?php
$pageTitle = 'Tambah Inventaris';
$breadcrumbs = [
    ['title' => 'Inventaris', 'url' => 'index.php'],
    ['title' => 'Tambah Inventaris', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

// Get categories
$categories = $func->getKategori();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_inventaris = $func->generateKodeInventaris();
    $nama_inventaris = $db->real_escape_string(trim($_POST['nama_inventaris']));
    $kategori_id = $db->real_escape_string($_POST['kategori_id']);
    $jumlah_tersedia = (int)$_POST['jumlah_tersedia'];
    $deskripsi = $db->real_escape_string(trim($_POST['deskripsi']));
    $foto = null;
    
    // Handle file upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $upload = $func->uploadFoto($_FILES['foto']);
        if ($upload['success']) {
            $foto = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    }
    
    if (empty($error)) {
        $query = "INSERT INTO inventaris (kode_inventaris, nama_inventaris, kategori_id, jumlah_tersedia, foto, deskripsi) 
                  VALUES ('$kode_inventaris', '$nama_inventaris', '$kategori_id', $jumlah_tersedia, " . 
                  ($foto ? "'$foto'" : "NULL") . ", '$deskripsi')";
        
        if ($db->query($query)) {
            header('Location: index.php?success=' . urlencode('Data inventaris berhasil ditambahkan'));
            exit();
        } else {
            $error = 'Gagal menambahkan data';
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
            <h5><i class="fas fa-plus"></i> Tambah Inventaris</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Inventaris <span style="color: red;">*</span></label>
                    <input type="text" name="nama_inventaris" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori <span style="color: red;">*</span></label>
                    <select name="kategori_id" class="form-control" required style="padding: 12px 15px;">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jumlah Tersedia <span style="color: red;">*</span></label>
                    <input type="number" name="jumlah_tersedia" class="form-control" min="0" value="0" required>
                </div>
                
                <div class="form-group">
                    <label>Foto</label>
                    <input type="file" name="foto" class="form-control" accept="image/*" onchange="previewImage(this, 'preview')">
                    <img id="preview" class="image-preview" style="display: none;">
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
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

<?php include __DIR__ . '/../views/footer.php'; ?>