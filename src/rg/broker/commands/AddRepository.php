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
        $repositoryDir = rtrim($input->getOption('base-dir'), '/') . '/'. $repositoryName;
        $repositoryUrl = rtrim($input->getOption('base-url'), '/') . '/'. $repositoryName;

        $output->writeln('Creating repository ' . $repositoryName);

        if (file_exists($repositoryDir)) {
            $processExecutor->execute('rm -rf ' . $repositoryDir);
        }
        mkdir($repositoryDir, 0777, true);
        mkdir($repositoryDir . '/sources');
        mkdir($repositoryDir . '/dists');

        putenv('COMPOSER_VENDOR_DIR=' . $repositoryDir . '/sources');
        putenv('COMPOSER_BIN_DIR=' . $repositoryDir . '/sources/bin');

        $output->writeln('Loading composer file ' . $composerUrl);

        $io = new \Composer\IO\ConsoleIO($input, $output, $this->getHelperSet());
        $composer = \Composer\Factory::create($io, $composerUrl);

        $composer->setLocker(new \rg\broker\customizations\Locker());
        $composer->getDownloadManager()->setDownloader('pear', new \rg\broker\customizations\PearDownloader($io));

        $installer = \Composer\Installer::create($io, $composer);
        $installer->setRunScripts(false);
        $installer->run();

        $packages = array();
        $dumper = new \Composer\Package\Dumper\ArrayDumper();

        $installedPackages = $this->getInstalledPackages($repositoryDir);
        foreach ($installedPackages as $installedPackage) {
            /** @var \Composer\Package\PackageInterface $package  */
            $package = $composer->getRepositoryManager()->findPackage($installedPackage['name'], $installedPackage['version']);
            $zipfileName = $this->createZipFile($repositoryDir, $package, $output, $processExecutor);
            $packageArray = $this->getPackageArray($repositoryDir, $repositoryUrl, $dumper, $package, $zipfileName);
            $packages['packages'][$package->getName()][$package->getVersion()] = $packageArray;
        }

        $output->writeln('Writing packages.json');
        $repoJson = new \Composer\Json\JsonFile($repositoryDir . '/packages.json');
        $repoJson->write($packages);

        // clean up sources
        $processExecutor->execute('rm -rf ' . escapeshellarg($repositoryDir . '/sources'));
    }

    /**
     * @param string $repositoryDir
     * @param string $repositoryUrl
     * @param \Composer\Package\Dumper\ArrayDumper $dumper
     * @param \Composer\Package\PackageInterface $package
     * @param string $zipfileName
     * @return array
     */
    protected function getPackageArray($repositoryDir,
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
	    throw new \Exception("No reference found");
	}

        unset($packageArray['installation-source']);
        unset($packageArray['source']);
        unset($packageArray['dist']);

        $packageArray['dist'] = array(
            'type' => 'zip',
            'url' => $repositoryUrl . '/dists/' . $zipfileName . '.zip',
            'reference' => $reference,
            'shasum' => hash_file('sha1', $repositoryDir . '/dists/' . $zipfileName . '.zip'),
        );

        return $packageArray;
    }

    /**
     * @param string $repositoryDir
     * @param \Composer\Package\PackageInterface $package
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Composer\Util\ProcessExecutor $process
     * @return string
     * @throws \Exception
     */
    protected function createZipFile($repositoryDir,
                                     \Composer\Package\PackageInterface $package,
                                     OutputInterface $output,
                                     \Composer\Util\ProcessExecutor $process) {
        $zipfileName = str_replace('/', '_', $package->getPrettyName());
        if ($package->getDistType() === 'pear') {
            $rootPath = $repositoryDir . '/sources';
            $zipPath = escapeshellarg($package->getPrettyName());
        } else if ($package->getTargetDir()) {
            $rootPath = $repositoryDir . '/sources/' . $package->getPrettyName() . '/' . $package->getTargetDir();
            $zipPath = '.';
        } else {
            $rootPath = $repositoryDir . '/sources/' . $package->getPrettyName();
            $zipPath = '.';
        }

        if (!class_exists('ZipArchive')) {
            $command = 'cd ' . escapeshellarg($rootPath) .
                ' && zip -9 -r ' . escapeshellarg($repositoryDir . '/dists/' . $zipfileName . '.zip') . ' ' . $zipPath;
            $output->writeln('Executing: ' . $command);
            $result = $process->execute($command);

            if ($result) {
                throw new \Exception('could not create dist package for ' . $package->getName());
            }
        } else {
            $zipFile = $repositoryDir . '/dists/' . $zipfileName . '.zip';
            $output->writeln("Use PHP ZipArchive to create Zip Archive: $zipFile");
            
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
     * @param string $repositoryDir
     * @return array
     * @throws \Exception
     */
    protected function getInstalledPackages($repositoryDir) {
        $file = new \Composer\Json\JsonFile($repositoryDir . '/sources/composer/installed.json');
        if (!$file->exists()) {
            throw new \Exception('no packages installed in repository');
        }
        return $file->read();
    }

}
