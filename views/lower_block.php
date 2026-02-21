<?php
$db = Database::getInstance()->getConnection();

// Get recent borrowings
$recentQuery = "SELECT p.*, u.username 
                FROM peminjaman p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT 5";
$recentResult = $db->query($recentQuery);

// Get low stock items
$lowStockQuery = "SELECT i.*, k.nama_kategori 
                  FROM inventaris i 
                  JOIN kategori k ON i.kategori_id = k.id 
                  WHERE i.jumlah_tersedia <= 5 
                  ORDER BY i.jumlah_tersedia ASC 
                  LIMIT 5";
$lowStockResult = $db->query($lowStockQuery);
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
    <!-- Recent Borrowings -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-history"></i> Peminjaman Terbaru</h5>
        </div>
        <div class="card-body">
            <?php if ($recentResult && $recentResult->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Peminjaman</th>
                                <th>Peminjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recentResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['nomor_peminjaman']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo $func->getStatusBadge($row['status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #7f8c8d;">Belum ada peminjaman</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Low Stock Items -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-exclamation-circle"></i> Stok Menipis</h5>
        </div>
        <div class="card-body">
            <?php if ($lowStockResult && $lowStockResult->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $lowStockResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_inventaris']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['jumlah_tersedia'] == 0 ? 'bg-danger' : 'bg-warning'; ?>">
                                            <?php echo $row['jumlah_tersedia']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #7f8c8d;">Semua stok aman</p>
            <?php endif; ?>
        </div>
    </div>
</div>