<?php
$pageTitle = 'Data Karyawan';
$breadcrumbs = [
    ['title' => 'Karyawan', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

// Get search parameter
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $db->real_escape_string($_GET['role']) : '';

// Build query with search
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM peminjaman WHERE user_id = u.id) as total_peminjaman,
          (SELECT COUNT(*) FROM peminjaman WHERE user_id = u.id AND status = 'disetujui') as peminjaman_aktif
          FROM users u 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (u.username LIKE '%$search%' OR u.departemen LIKE '%$search%')";
}

if (!empty($role_filter)) {
    $query .= " AND u.role = '$role_filter'";
}

$query .= " ORDER BY u.created_at DESC";

$result = $db->query($query);

// Get statistics
$totalKaryawan = $db->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalAdmin = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];
$totalPegawai = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'pegawai'")->fetch_assoc()['total'];
?>

<?php include __DIR__ . '/../views/sidebar.php'; ?>
<?php include __DIR__ . '/../views/topnav.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../views/breadcrumb.php'; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <?php echo $func->getAlert('success', $_GET['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <?php echo $func->getAlert('danger', $_GET['error']); ?>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 25px;">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalKaryawan; ?></h3>
                <p>Total Karyawan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalAdmin; ?></h3>
                <p>Administrator</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalPegawai; ?></h3>
                <p>Pegawai</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-users"></i> Data Karyawan</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Karyawan
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" action="" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="search" class="form-control" placeholder="Cari username atau departemen..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 300px;">
                    
                    <select name="role" class="form-control" style="max-width: 200px; padding: 12px 15px;">
                        <option value="">Semua Role</option>
                        <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="pegawai" <?php echo $role_filter == 'pegawai' ? 'selected' : ''; ?>>Pegawai</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    
                    <?php if (!empty($search) || !empty($role_filter)): ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Departemen</th>
                            <th>Total Peminjaman</th>
                            <th>Peminjaman Aktif</th>
                            <th>Terdaftar</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #3498db, #2980b9); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                            </div>
                                            <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row['role'] == 'admin'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-user-shield"></i> Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-user-tie"></i> Pegawai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['departemen'] ? htmlspecialchars($row['departemen']) : '-'; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $row['total_peminjaman']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['peminjaman_aktif'] > 0): ?>
                                            <span class="badge bg-warning"><?php echo $row['peminjaman_aktif']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Hapus karyawan ini? Semua data peminjaman terkait akan ikut terhapus.')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #7f8c8d;">
                                    <?php echo !empty($search) || !empty($role_filter) ? 'Data tidak ditemukan' : 'Belum ada data karyawan'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>