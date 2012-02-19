<?php /** @var \Composer\Package\PackageInterface $package  */ ?>
<div class="row-fluid">
<div class="well">
    <h2>
        <?php echo $package->getPrettyName(); ?>
        <small><?php echo $package->getPrettyVersion(); ?></small>
    </h2>
    <?php if ($package->getDescription()): ?>
    <p><?php echo $package->getDescription(); ?></p>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <tbody>
        <?php if ($package->getType()): ?>
        <tr>
            <th>Type</th>
            <td>
                <?php echo ucfirst($package->getType()); ?>
            </td>
        </tr>
            <?php endif;?>
        <?php if ($package->getAuthors()): ?>
        <tr>
            <th>Authors</th>
            <td>
                <?php foreach ($package->getAuthors() as $author): ?>
                <?php echo $author['name']; ?>
                <?php if (isset($author['email'])): ?>
                    <a href="mailto:<?php echo $author['email']; ?>" target="_blank">
                        <?php echo $author['email']; ?>
                    </a>
                    <?php endif; ?>
                <?php if (isset($author['homepage'])): ?>
                    <a href="<?php echo $author['homepage']; ?>" target="_blank">
                        <?php echo $author['homepage']; ?>
                    </a>
                    <?php endif; ?>
                <br/>
                <?php endforeach; ?>
            </td>
        </tr>
            <?php endif;?>
        <?php if ($package->getLicense()): ?>
        <tr>
            <th>License</th>
            <td>
                <?php echo implode(', ', $package->getLicense()); ?>
            </td>
        </tr>
            <?php endif;?>
        <?php if ($package->getKeywords()): ?>
        <tr>
            <th>Keywords</th>
            <td>
                <?php echo implode(', ', $package->getKeywords()); ?>
            </td>
        </tr>
            <?php endif;?>
        <?php if ($package->getHomepage()): ?>
        <tr>
            <th>Homepage</th>
            <td>
                <a href="<?php echo $package->getHomepage(); ?>" target="_blank">
                    <?php echo $package->getHomepage(); ?>
                </a>
            </td>
        </tr>
            <?php endif;?>
        <?php if ($package->getReleaseDate()): ?>
        <tr>
            <th>Release Date</th>
            <td>
                <?php echo $package->getReleaseDate()->format('Y-m-d'); ?>
            </td>
        </tr>
            <?php endif;?>
        <?php if ($package->getDistUrl()): ?>
        <tr>
            <th>Download URL</th>
            <td>
                <a href="<?php echo $package->getDistUrl(); ?>">
                    <?php echo $package->getDistUrl(); ?>
                </a>
            </td>
        </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>