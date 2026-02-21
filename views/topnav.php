<div class="topnav">
    <div class="topnav-left">
        <h4><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h4>
    </div>
    
    <div class="topnav-right">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                <small><?php echo ucfirst($user['role']); ?><?php echo $user['departemen'] ? ' - ' . htmlspecialchars($user['departemen']) : ''; ?></small>
            </div>
        </div>
        
        <a href="<?php echo $basePath; ?>/logout.php" class="btn-logout" onclick="return confirm('Yakin ingin logout?')">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</div>