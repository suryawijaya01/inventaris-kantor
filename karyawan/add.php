<?php
$pageTitle = 'Tambah Karyawan';
$breadcrumbs = [
    ['title' => 'Karyawan', 'url' => 'index.php'],
    ['title' => 'Tambah Karyawan', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $db->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $db->real_escape_string($_POST['role']);
    $departemen = $db->real_escape_string(trim($_POST['departemen']));
    
    if (empty($username) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = 'Username, password, dan role harus diisi';
    } elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok';
    } else {
        // Check if username already exists
        $checkQuery = "SELECT id FROM users WHERE username = '$username'";
        $checkResult = $db->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username sudah digunakan';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, password, role, departemen) 
                      VALUES ('$username', '$hashed_password', '$role', " . 
                      ($departemen ? "'$departemen'" : "NULL") . ")";
            
            if ($db->query($query)) {
                header('Location: index.php?success=' . urlencode('Karyawan berhasil ditambahkan'));
                exit();
            } else {
                $error = 'Gagal menambahkan karyawan';
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
            <h5><i class="fas fa-plus"></i> Tambah Karyawan</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username <span style="color: red;">*</span></label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                    <small style="color: #7f8c8d;">Minimal 4 karakter</small>
                </div>
                
                <div class="form-group">
                    <label>Password <span style="color: red;">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    <small style="color: #7f8c8d;">Minimal 6 karakter</small>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password <span style="color: red;">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi password" required>
                </div>
                
                <div class="form-group">
                    <label>Role <span style="color: red;">*</span></label>
                    <select name="role" class="form-control" required style="padding: 12px 15px;">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="pegawai">Pegawai</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Departemen</label>
                    <input type="text" name="departemen" class="form-control" placeholder="Masukkan departemen (opsional)">
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