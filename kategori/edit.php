<?php
$pageTitle = 'Edit Kategori';
$breadcrumbs = [
    ['title' => 'Kategori', 'url' => 'index.php'],
    ['title' => 'Edit Kategori', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$error = '';

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get current data
$query = "SELECT * FROM kategori WHERE id = $id";
$result = $db->query($query);

if (!$result || $result->num_rows === 0) {
    header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    exit();
}

$data = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = $db->real_escape_string(trim($_POST['nama_kategori']));
    
    if (empty($nama_kategori)) {
        $error = 'Nama kategori harus diisi';
    } else {
        // Check if category already exists (except current)
        $checkQuery = "SELECT id FROM kategori WHERE nama_kategori = '$nama_kategori' AND id != $id";
        $checkResult = $db->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $error = 'Kategori sudah ada';
        } else {
            $query = "UPDATE kategori SET nama_kategori = '$nama_kategori' WHERE id = $id";
            
            if ($db->query($query)) {
                header('Location: index.php?success=' . urlencode('Kategori berhasil diupdate'));
                exit();
            } else {
                $error = 'Gagal mengupdate kategori';
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
            <h5><i class="fas fa-edit"></i> Edit Kategori</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Kategori <span style="color: red;">*</span></label>
                    <input type="text" name="nama_kategori" class="form-control" value="<?php echo htmlspecialchars($data['nama_kategori']); ?>" required autofocus>
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