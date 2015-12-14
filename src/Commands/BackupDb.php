<?php
namespace app\Commands;

use app\Config;
use app\DbManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupDb extends Command
{

    protected function configure()
    {
        $this->setName('backup:db')
          ->setDescription('Create a backup for the specified database connection.')
          ->addArgument('connection', InputArgument::REQUIRED, 'Database connection to use for backup')
          ->addArgument('target', InputArgument::REQUIRED, 'Filesystem that should be used to save the backup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if the config files exist.
        if (!Config::check()) {
            $output->writeln('<error>Missing config files</error>');

            return;
        }

        // Arguments.
        $db = $input->getArgument('connection');
        $filesystem = $input->getArgument('target');

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

        // Destination.
        $destination = 'databases/'.$db.'/'.$db.'_'.date('d-m-Y').'_'.uniqid().'.sql';

        // Get DB backup manager.
        $manager = DbManager::get();

        // Execute the backup task.
        try {
            $manager->makeBackup()->run($db, $filesystem, $destination, 'gzip');
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return;
        }

        // If we get till here, backup was successful.
        $output->writeln('<info>Database backup finished.</info>');
    }


}