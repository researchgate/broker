<?php
/*
 * This file is part of rg\broker.
 *
 * (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace rg\broker\commands;

use rg\broker\customizations\ZipArchive;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddRepository extends \Symfony\Component\Console\Command\Command {

    protected function configure() {
        $this
            ->setName('broker:add')
            ->setAliases(array('broker:build'))
            ->setDescription('adds a new repository based on a composer json file')
            ->setDefinition(array(
                new \Symfony\Component\Console\Input\InputArgument('name', \Symfony\Component\Console\Input\InputArgument::REQUIRED),
                new \Symfony\Component\Console\Input\InputArgument('composerUrl', \Symfony\Component\Console\Input\InputArgument::REQUIRED),
                new InputOption('clean-cache', null, InputOption::VALUE_NONE, 'If set, cache will be removed first'),
                new InputOption('cache-dir', null, InputOption::VALUE_REQUIRED, 'Where to put cached sources?', ROOT . '/cache/'),
                new InputOption('base-dir', null, InputOption::VALUE_REQUIRED, 'Where to put generated files (packages.json and dists)?', ROOT . '/repositories/'),
                new InputOption('base-url', null, InputOption::VALUE_REQUIRED, 'Base URL used when accessing packages.json and dists', ROOTURL . '/repositories/'),
            ))
            ->setHelp('adds a new repository based on a composer json file');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $processExecutor = new \Composer\Util\ProcessExecutor();

        $composerUrl = $input->getArgument('composerUrl');
        $repositoryName = $input->getArgument('name');
        $repositoryUrl = rtrim($input->getOption('base-url'), '/') . '/'. $repositoryName;
        $targetDir = rtrim($input->getOption('base-dir'), '/') . '/'. $repositoryName;
        $cacheDir = rtrim($input->getOption('cache-dir'), '/') . '/'. $repositoryName;

        if ($input->getOption('clean-cache')) {
            $output->writeln('Cleaning cached sources');
            $processExecutor->execute('rm -rf ' . escapeshellarg($cacheDir));
        }

        $output->writeln(sprintf('Creating repository "%s"', $repositoryName));

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        putenv('COMPOSER_VENDOR_DIR=' . $cacheDir);
        putenv('COMPOSER_BIN_DIR=' . $cacheDir . '/bin');

        $output->writeln('Loading composer file ' . $composerUrl);

        $json = new \Composer\Json\JsonFile($composerUrl);
        $jsonData = $originalJsonData = $json->read();

        // if 'require-only-dependencies-of' is found, rewrite json data to composer format
        if (isset($originalJsonData['require-only-dependencies-of'])) {
            $jsonData['require'] += $jsonData['require-only-dependencies-of'];
            unset($jsonData['require-only-dependencies-of']);

            $composerUrl = $cacheDir.'/broker.json';
            $composerJson = new \Composer\Json\JsonFile($composerUrl);
            $composerJson->write($jsonData);
        }

        $io = new \Composer\IO\ConsoleIO($input, $output, $this->getHelperSet());
        $composer = \Composer\Factory::create($io, $composerUrl);

        $composer->setLocker(new \rg\broker\customizations\Locker());
        $composer->getDownloadManager()->setDownloader('pear', new \rg\broker\customizations\PearDownloader($io));

        $installer = \Composer\Installer::create($io, $composer);
        $installer->setRunScripts(false);
        $installer->setPreferDist(true);
        $installer->setUpdate(true);

        if (!$installer->run()) {
            $output->writeln('Composer installer failed!');
            return 1;
        }

        if (file_exists($targetDir)) {
            $processExecutor->execute('rm -rf ' . $targetDir);
        }
        mkdir($targetDir, 0777, true);
        mkdir($targetDir . '/dists');

        $output->writeln('Creating dists for packages');

        $packages = array('packages' => array());
        $dumper = new \Composer\Package\Dumper\ArrayDumper();

        $installedPackages = $this->getInstalledPackages($cacheDir);
        $localRepos = new \Composer\Repository\CompositeRepository($composer->getRepositoryManager()->getLocalRepositories());
        foreach ($installedPackages as $installedPackage) {
            /** @var \Composer\Package\PackageInterface $package  */
            $package = $localRepos->findPackage($installedPackage['name'], $installedPackage['version']);

            // skip if package was in 'require-only-dependencies-of' section
            if (isset($originalJsonData['require-only-dependencies-of'][$package->getName()])
             || isset($originalJsonData['require-only-dependencies-of'][$package->getPrettyName()])) {
                continue;
            }

            $zipfileName = $this->createZipFile($cacheDir, $targetDir, $package, $output, $processExecutor);
            $packageArray = $this->getPackageArray($targetDir, $repositoryUrl, $dumper, $package, $zipfileName);
            $packages['packages'][$package->getPrettyName()][$package->getPrettyVersion()] = $packageArray;
        }

        ksort($packages['packages'], SORT_STRING);

        $output->writeln('Writing packages.json');
        $repoJson = new \Composer\Json\JsonFile($targetDir . '/packages.json');
        $repoJson->write($packages);
    }

    /**
     * @param string $targetDir
     * @param string $repositoryUrl
     * @param \Composer\Package\Dumper\ArrayDumper $dumper
     * @param \Composer\Package\PackageInterface $package
     * @param string $zipfileName
     * @return array
     */
    protected function getPackageArray($targetDir,
                                       $repositoryUrl,
                                       \Composer\Package\Dumper\ArrayDumper $dumper,
                                       \Composer\Package\PackageInterface $package,
                                       $zipfileName) {
        $packageArray = $dumper->dump($package);
	if (!empty($packageArray['dist']['reference'])) {
            $reference = $packageArray['dist']['reference'];
	} else if (!empty($packageArray['source']['reference'])) {
	    $reference = $packageArray['source']['reference'];
	} else {
        $reference = ''; // e.g. zend packages
	}

        unset($packageArray['installation-source']);
        unset($packageArray['source']);
        unset($packageArray['dist']);

        $packageArray['dist'] = array(
            'type' => 'zip',
            'url' => $repositoryUrl . '/dists/' . $zipfileName . '.zip',
            'reference' => $reference,
            'shasum' => hash_file('sha1', $targetDir . '/dists/' . $zipfileName . '.zip'),
        );

        return $packageArray;
    }

    /**
     * @param string $cacheDir
     * @param string $targetDir
     * @param \Composer\Package\PackageInterface $package
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Composer\Util\ProcessExecutor $process
     * @return string
     * @throws \Exception
     */
    protected function createZipFile($cacheDir,
                                     $targetDir,
                                     \Composer\Package\PackageInterface $package,
                                     OutputInterface $output,
                                     \Composer\Util\ProcessExecutor $process) {
        $zipfileName = str_replace('/', '_', $package->getPrettyName());
        if ($package->getDistType() === 'pear') {
            $rootPath = $cacheDir;
            $zipPath = escapeshellarg($package->getPrettyName());
        } else if ($package->getTargetDir()) {
            $rootPath = $cacheDir . '/' . $package->getPrettyName() . '/' . $package->getTargetDir();
            $zipPath = '.';
        } else {
            $rootPath = $cacheDir . '/' . $package->getPrettyName();
            $zipPath = '.';
        }

        $output->writeln('  - '.$zipfileName . '.zip');

        if (!class_exists('ZipArchive')) {
            $command = 'cd ' . escapeshellarg($rootPath) .
                ' && zip -9 -r ' . escapeshellarg($targetDir . '/dists/' . $zipfileName . '.zip') . ' ' . $zipPath;
            $result = $process->execute($command);

            if ($result) {
                throw new \Exception('could not create dist package for ' . $package->getName());
            }
        } else {
            $zipFile = $targetDir . '/dists/' . $zipfileName . '.zip';

            $zipArchive = new ZipArchive();
            $zipArchive->addExcludeDirectory('.svn');
            $zipArchive->setArchiveBaseDir($rootPath);

            if (($result = $zipArchive->open($zipFile, ZipArchive::OVERWRITE)) === TRUE) {
                $zipArchive->addDir($rootPath);
                
                $zipArchive->close();
            } else {
                throw new \Exception('could not create dist package for ' . $package->getName() . ' error code: ' . $result);
            }
        }

        return $zipfileName;
    }

    /**
     * @param string $cacheDir
     * @return array
     * @throws \Exception
     */
    protected function getInstalledPackages($cacheDir) {
        $file = new \Composer\Json\JsonFile($cacheDir . '/composer/installed.json');
        if (!$file->exists()) {
            throw new \Exception('no packages installed in repository');
        }
        return $file->read();
    }

}
