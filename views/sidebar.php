<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Load menu from JSON
$menuFile = __DIR__ . '/../config/menu.json';
$menuData = json_decode(file_get_contents($menuFile), true);
$menuItems = $menuData[$user['role']] ?? $menuData['pegawai'];
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h3><i class="fas fa-box"></i> INVENTARIS</h3>
        <small>Sistem Manajemen Inventaris</small>
    </div>
    
    <div class="sidebar-menu">
        <?php foreach ($menuItems as $menu): ?>
            <?php 
            $isActive = in_array($currentDir, $menu['active']);
            $activeClass = $isActive ? 'active' : '';
            ?>
            <div class="menu-item">
                <a href="<?php echo $basePath . $menu['url']; ?>" class="<?php echo $activeClass; ?>">
                    <i class="<?php echo $menu['icon']; ?>"></i>
                    <span><?php echo $menu['title']; ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>