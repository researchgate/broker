<?php
/*
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
    /**
     * switch type to library since otherwise the extraction of the cache does not work as it is supposed to
     *
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if (method_exists($package, 'setType')) {
            /* @var $package \Composer\Package\CompletePackage */
            $package->setType('library');
        }

        return parent::install($repo, $package);
    }

    /**
     * switch type to library since otherwise the extraction of the cache does not work as it is supposed to
     *
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        if (method_exists($target, 'setType')) {
            /* @var $package \Composer\Package\CompletePackage */
            $package->setType('library');
        }

        return parent::update($repo, $initial, $target);
    }

    /**
     * add pear binary list to package, in order to generate binary entries in the resulting packages.json file
     *
     * @param PackageInterface $package
     *
     * @return array
     */
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
