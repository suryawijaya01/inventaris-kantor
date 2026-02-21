<?php
$breadcrumbs = isset($breadcrumbs) ? $breadcrumbs : [];
?>

<?php if (!empty($breadcrumbs)): ?>
<div class="breadcrumb-custom">
    <ol>
        <li><a href="<?php echo $basePath; ?>/admin/index.php"><i class="fas fa-home"></i> Home</a></li>
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php if ($index === count($breadcrumbs) - 1): ?>
                <li class="active"><?php echo $crumb['title']; ?></li>
            <?php else: ?>
                <li><a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['title']; ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</div>
<?php endif; ?>