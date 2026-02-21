<?php
$pageTitle = 'Data Inventaris';
$breadcrumbs = [
    ['title' => 'Inventaris', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

$db = Database::getInstance()->getConnection();

// Get search parameter
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';

// Get stock filter
$stokFilter = isset($_GET['stok']) ? $_GET['stok'] : '';

// Build query with search
$query = "SELECT i.*, k.nama_kategori 
          FROM inventaris i 
          JOIN kategori k ON i.kategori_id = k.id";

$conditions = [];

if (!empty($search)) {
    $conditions[] = "(i.kode_inventaris LIKE '%$search%' OR i.nama_inventaris LIKE '%$search%' OR k.nama_kategori LIKE '%$search%')";
}

if ($stokFilter == 'menipis') {
    $conditions[] = "i.jumlah_tersedia <= 5 AND i.jumlah_tersedia > 0";
} elseif ($stokFilter == 'habis') {
    $conditions[] = "i.jumlah_tersedia = 0";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY i.id DESC";

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
            <h5><i class="fas fa-box"></i> Data Inventaris</h5>
            <?php if ($auth->isAdmin()): ?>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Inventaris
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, atau kategori..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 400px;">
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
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Inventaris</th>
                            <th>Kategori</th>
                            <th>Jumlah Tersedia</th>
                            <th>Foto</th>
                            <th>Deskripsi</th>
                            <?php if ($auth->isAdmin()): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['kode_inventaris']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['nama_inventaris']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['jumlah_tersedia'] == 0 ? 'bg-danger' : ($row['jumlah_tersedia'] <= 5 ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $row['jumlah_tersedia']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['foto']): ?>
                                            <img src="<?php echo $basePath; ?>/uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <span style="color: #7f8c8d;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo $row['deskripsi'] ? htmlspecialchars($row['deskripsi']) : '-'; ?>
                                    </td>
                                    <?php if ($auth->isAdmin()): ?>
                                        <td>
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $auth->isAdmin() ? '8' : '7'; ?>" style="text-align: center; color: #7f8c8d;">
                                    <?php echo !empty($search) ? 'Data tidak ditemukan' : 'Belum ada data inventaris'; ?>
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