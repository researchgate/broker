<?php
namespace rg\broker\commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveRepository extends \Symfony\Component\Console\Command\Command {

    protected function configure() {
        $this
            ->setName('broker:remove')
            ->setDescription('removes a repository')
            ->setDefinition(array(
                new \Symfony\Component\Console\Input\InputArgument('name', \Symfony\Component\Console\Input\InputArgument::REQUIRED),
            ))
            ->setHelp('removes a repository');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $processExecutor = new \Composer\Util\ProcessExecutor();

        $repositoryName = $input->getArgument('name');
        $repositoryDir = ROOT . '/repositories/' . $repositoryName;

        if (file_exists($repositoryDir)) {
            $output->writeln('Removing repository ' . $repositoryName);
            $processExecutor->execute('rm -rf ' . $repositoryDir);
        } else {
            throw new \Exception('Repository ' . $repositoryName . ' does not exist.');
        }
    }

}