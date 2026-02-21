<?php
$pageTitle = 'Edit Karyawan';
$breadcrumbs = [
    ['title' => 'Karyawan', 'url' => 'index.php'],
    ['title' => 'Edit Karyawan', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$error = '';

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get current data
$query = "SELECT * FROM users WHERE id = $id";
$result = $db->query($query);

if (!$result || $result->num_rows === 0) {
    header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    exit();
}

$data = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $db->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $db->real_escape_string($_POST['role']);
    $departemen = $db->real_escape_string(trim($_POST['departemen']));
    
    if (empty($username) || empty($role)) {
        $error = 'Username dan role harus diisi';
    } elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok';
    } else {
        // Check if username already exists (except current user)
        $checkQuery = "SELECT id FROM users WHERE username = '$username' AND id != $id";
        $checkResult = $db->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username sudah digunakan';
        } else {
            $query = "UPDATE users SET 
                      username = '$username',
                      role = '$role',
                      departemen = " . ($departemen ? "'$departemen'" : "NULL");
            
            // Only update password if provided
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password = '$hashed_password'";
            }
            
            $query .= " WHERE id = $id";
            
            if ($db->query($query)) {
                header('Location: index.php?success=' . urlencode('Data karyawan berhasil diupdate'));
                exit();
            } else {
                $error = 'Gagal mengupdate data karyawan';
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
            <h5><i class="fas fa-edit"></i> Edit Karyawan</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username <span style="color: red;">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($data['username']); ?>" required autofocus>
                    <small style="color: #7f8c8d;">Minimal 4 karakter</small>
                </div>
                
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <small style="color: #7f8c8d;">Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password</small>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi password baru">
                </div>
                
                <div class="form-group">
                    <label>Role <span style="color: red;">*</span></label>
                    <select name="role" class="form-control" required style="padding: 12px 15px;">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" <?php echo $data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="pegawai" <?php echo $data['role'] == 'pegawai' ? 'selected' : ''; ?>>Pegawai</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Departemen</label>
                    <input type="text" name="departemen" class="form-control" value="<?php echo htmlspecialchars($data['departemen']); ?>" placeholder="Masukkan departemen (opsional)">
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Info:</strong> Terdaftar sejak <?php echo date('d F Y', strtotime($data['created_at'])); ?>
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