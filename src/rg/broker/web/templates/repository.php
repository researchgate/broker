<?php require 'repository_header.php'; ?>

<?php foreach ($currentRepository->getPackages() as $package):?>
    <?php require 'package_item.php'; ?>
<?php endforeach; ?>


