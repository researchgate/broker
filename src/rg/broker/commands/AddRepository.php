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
use Symfony\Component\Console\Output\OutputInterface;

class AddRepository extends \Symfony\Component\Console\Command\Command {

    protected function configure() {
        $this
            ->setName('broker:add')
            ->setDescription('adds a new repository based on a composer json file')
            ->setDefinition(array(
                new \Symfony\Component\Console\Input\InputArgument('name', \Symfony\Component\Console\Input\InputArgument::REQUIRED),
                new \Symfony\Component\Console\Input\InputArgument('composerUrl', \Symfony\Component\Console\Input\InputArgument::REQUIRED),
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
        $repositoryDir = ROOT . '/repositories/' . $repositoryName;

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
        $installer->run();

        $packages = array();
        $dumper = new \Composer\Package\Dumper\ArrayDumper();

        $installedPackages = $this->getInstalledPackages($repositoryDir);
        foreach ($installedPackages as $installedPackage) {
            /** @var \Composer\Package\PackageInterface $package  */
            $package = $composer->getRepositoryManager()->findPackage($installedPackage['name'], $installedPackage['version']);
            $zipfileName = $this->createZipFile($repositoryDir, $package, $output, $processExecutor);
            $packageArray = $this->getPackageArray($repositoryName, $dumper, $package, $zipfileName);
            $packages[$package->getName()]['versions'][$package->getVersion()] = $packageArray;
        }

        $output->writeln('Writing packages.json');
        $repoJson = new \Composer\Json\JsonFile($repositoryDir . '/packages.json');
        $repoJson->write($packages);
    }

    /**
     * @param string $repositoryName
     * @param \Composer\Package\Dumper\ArrayDumper $dumper
     * @param \Composer\Package\PackageInterface $package
     * @param string $zipfileName
     * @return array
     */
    protected function getPackageArray($repositoryName,
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

        unset($packageArray['source']);
        unset($packageArray['dist']);

        // we have to manipulate the version to not have a dev prefix or suffix so that
        // composer does not try to load the package from source but will load it from dist instead
	    if ($package->isDev()) {
            $packageArray['version'] = str_replace('-dev', '', $packageArray['version']);
            $packageArray['version'] = str_replace('dev-', '', $packageArray['version']);
            $packageArray['version'] = str_replace('x', '9999999', $packageArray['version']);
            $packageArray['version_normalized'] = $packageArray['version'];
        }

        foreach ($packageArray['require'] as $requiredPackage => $requiredVersion) {
            if ($requiredPackage === 'php') {
                continue;
            }
            $packageArray['require'][$requiredPackage] = '*';
        }
        $packageArray['dist'] = array(
            'type' => 'zip',
            'url' => ROOTURL . '/repositories/' . $repositoryName . '/dists/' . $zipfileName . '.zip',
            'reference' => $reference,
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
