<?php
$pageTitle = 'Edit Inventaris';
$breadcrumbs = [
    ['title' => 'Inventaris', 'url' => 'index.php'],
    ['title' => 'Edit Inventaris', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get current data
$query = "SELECT * FROM inventaris WHERE id = $id";
$result = $db->query($query);

if (!$result || $result->num_rows === 0) {
    header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    exit();
}

$data = $result->fetch_assoc();

// Get categories
$categories = $func->getKategori();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_inventaris = $db->real_escape_string(trim($_POST['nama_inventaris']));
    $kategori_id = $db->real_escape_string($_POST['kategori_id']);
    $jumlah_tersedia = (int)$_POST['jumlah_tersedia'];
    $deskripsi = $db->real_escape_string(trim($_POST['deskripsi']));
    $foto = $data['foto'];
    
    // Handle file upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $upload = $func->uploadFoto($_FILES['foto'], $data['foto']);
        if ($upload['success']) {
            $foto = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    }
    
    if (empty($error)) {
        $query = "UPDATE inventaris SET 
                  nama_inventaris = '$nama_inventaris',
                  kategori_id = '$kategori_id',
                  jumlah_tersedia = $jumlah_tersedia,
                  foto = " . ($foto ? "'$foto'" : "NULL") . ",
                  deskripsi = '$deskripsi'
                  WHERE id = $id";
        
        if ($db->query($query)) {
            header('Location: index.php?success=' . urlencode('Data inventaris berhasil diupdate'));
            exit();
        } else {
            $error = 'Gagal mengupdate data';
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
            <h5><i class="fas fa-edit"></i> Edit Inventaris</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Kode Inventaris</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['kode_inventaris']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label>Nama Inventaris <span style="color: red;">*</span></label>
                    <input type="text" name="nama_inventaris" class="form-control" value="<?php echo htmlspecialchars($data['nama_inventaris']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori <span style="color: red;">*</span></label>
                    <select name="kategori_id" class="form-control" required style="padding: 12px 15px;">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $data['kategori_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jumlah Tersedia <span style="color: red;">*</span></label>
                    <input type="number" name="jumlah_tersedia" class="form-control" min="0" value="<?php echo $data['jumlah_tersedia']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Foto</label>
                    <?php if ($data['foto']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?php echo $basePath; ?>/uploads/<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto" style="max-width: 200px; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto" class="form-control" accept="image/*" onchange="previewImage(this, 'preview')">
                    <img id="preview" class="image-preview" style="display: none;">
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
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