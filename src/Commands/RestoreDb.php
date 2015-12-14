<?php
namespace app\Commands;

use app\Config;
use app\DbManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreDb extends Command
{

    protected function configure()
    {
        $this->setName('restore:db')
          ->setDescription('Restore a database backup for the specified connection.')
          ->addArgument('filesystem', InputArgument::REQUIRED, 'Filesystem to retrieve the database backup')
          ->addArgument('path', InputArgument::REQUIRED, 'Path of the backup file')
          ->addArgument('connection', InputArgument::REQUIRED, 'Database connection that should be used to restore the database backup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if the config files exist.
        if (!Config::check()) {
            $output->writeln('<error>Missing config files</error>');

            return;
        }

        // Arguments.
        $filesystem = $input->getArgument('filesystem');
        $path = $input->getArgument('path');
        $db = $input->getArgument('connection');

        // Check if the db connection config exists.
        if (!Config::checkKey($db, 'db')) {
            $output->writeln("<error>The db connection '$db' was not found.</error>");

            return;
        }

        // Check if the filesystem config exists.
        if (!Config::checkKey($filesystem, 'filesystem')) {
            $output->writeln("<error>The target '$filesystem' config was not found.</error>");

            return;
        }

        // Get DB backup manager.
        $manager = DbManager::get();

        // Execute the restore task.
        try {
            $manager->makeRestore()->run($filesystem, $path, $db, 'gzip');
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return;
        }

        // If we get till here, restore was successful.
        $output->writeln('<info>Database restore finished.</info>');

    }


}