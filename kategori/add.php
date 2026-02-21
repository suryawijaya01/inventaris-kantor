<?php
$pageTitle = 'Tambah Kategori';
$breadcrumbs = [
    ['title' => 'Kategori', 'url' => 'index.php'],
    ['title' => 'Tambah Kategori', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = $db->real_escape_string(trim($_POST['nama_kategori']));
    
    if (empty($nama_kategori)) {
        $error = 'Nama kategori harus diisi';
    } else {
        // Check if category already exists
        $checkQuery = "SELECT id FROM kategori WHERE nama_kategori = '$nama_kategori'";
        $checkResult = $db->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $error = 'Kategori sudah ada';
        } else {
            $query = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
            
            if ($db->query($query)) {
                header('Location: index.php?success=' . urlencode('Kategori berhasil ditambahkan'));
                exit();
            } else {
                $error = 'Gagal menambahkan kategori';
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
            <h5><i class="fas fa-plus"></i> Tambah Kategori</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Kategori <span style="color: red;">*</span></label>
                    <input type="text" name="nama_kategori" class="form-control" placeholder="Masukkan nama kategori" required autofocus>
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