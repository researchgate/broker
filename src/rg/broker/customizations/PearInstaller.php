<?php

/*
 * This file is part of the Voycer ${PROJECT}.
 *
 * (c) Voycer AG <info@voycer.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace rg\broker\customizations;

use \Composer\Repository\InstalledRepositoryInterface;
use \Composer\Package\PackageInterface;
use \Composer\Downloader\PearPackageExtractor;
use \Composer\Installer\PearInstaller as ComposerPearInstaller;
/**
 * @author Nino Wagensonner <n.wagensonner@voycer.com>
 */
class PearInstaller extends ComposerPearInstaller
{
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if (method_exists($package, 'setType')) {
            /* @var $package \Composer\Package\CompletePackage */
            $package->setType('library');
        }

        parent::install($repo, $package);
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        if (method_exists($target, 'setType')) {
            /* @var $package \Composer\Package\CompletePackage */
            $package->setType('library');
        }

        parent::update($repo, $initial, $target);
    }


    protected function getBinaries(PackageInterface $package)
    {
        $binaries = parent::getBinaries($package);

        if (method_exists($package, 'setBinaries')) {
            /* @var $package \Composer\Package\CompletePackage */
            $package->setBinaries($binaries);
        }

        return $binaries;
    }
}
