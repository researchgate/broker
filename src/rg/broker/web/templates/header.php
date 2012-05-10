<?php
/*
* This file is part of rg\broker.
*
* (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>broker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="<?php echo ROOTURL; ?>/assets/css/bootstrap.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
        }
    </style>
    <link href="<?php echo ROOTURL; ?>/assets/css/bootstrap-responsive.css" rel="stylesheet">

    <link href="<?php echo ROOTURL; ?>/assets/css/prettify.css" rel="stylesheet">
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="<?php echo ROOTURL; ?>">broker</a>

            <!--/.nav-collapse -->
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span3">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">
                        Repositories
                    </li>
                    <?php foreach ($repositories as $repository): ?>
                    <?php if (isset($currentRepository)
                        && $currentRepository->getName() === $repository):?>
                    <li class="active">
                    <?php else: ?>
                    <li>
                    <?php endif; ?>
                        <a href="<?php echo ROOTURL; ?>/repositories/<?php echo $repository; ?>">
                            <?php echo $repository; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <?php if (isset($currentRepository)): ?>
                    <li class="nav-header">
                        Packages
                    </li>
                    <?php foreach ($currentRepository->getPackages() as $repositorypackage): ?>
                    <?php if (isset($package)
                            && $package->getName() === $repositorypackage->getName()
                        ):?>
                    <li class="active">
                    <?php else: ?>
                    <li>
                    <?php endif; ?>
                        <a href="<?php echo ROOTURL; ?>/repositories/<?php echo $currentRepository->getName(); ?>/package/<?php echo urlencode(urlencode($repositorypackage->getName())); ?>">
                            <?php echo $repositorypackage->getPrettyName(); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <!--/.well -->
        </div>
        <!--/span-->
        <div class="span9">

