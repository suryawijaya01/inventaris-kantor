<?php
$pageTitle = 'Data Kategori';
$breadcrumbs = [
    ['title' => 'Kategori', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

// Get search parameter
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';

// Build query with search
$query = "SELECT k.*, 
          (SELECT COUNT(*) FROM inventaris WHERE kategori_id = k.id) as total_inventaris 
          FROM kategori k";

if (!empty($search)) {
    $query .= " WHERE k.nama_kategori LIKE '%$search%'";
}

$query .= " ORDER BY k.nama_kategori ASC";

$result = $db->query($query);
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
    
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-tags"></i> Data Kategori</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Kategori
            </a>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama kategori..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 400px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (!empty($search)): ?>
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
                            <th style="width: 80px;">No</th>
                            <th>Nama Kategori</th>
                            <th style="width: 150px;">Total Inventaris</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['nama_kategori']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $row['total_inventaris']; ?> Item
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #7f8c8d;">
                                    <?php echo !empty($search) ? 'Data tidak ditemukan' : 'Belum ada data kategori'; ?>
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