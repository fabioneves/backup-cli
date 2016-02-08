<?php
namespace BackupCli\Commands;

use BackupCli\Services\BackupService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupDb extends Command
{

    protected function configure()
    {
        $this->setName('backup:db')
          ->setDescription('Backup a database and compress it.')
          ->addArgument('database', InputArgument::REQUIRED, 'Database to backup (needs to exist in config).')
          ->addArgument('target', InputArgument::REQUIRED, 'Target filesystem where the backup will be saved.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Show message of database backup start.
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("\n  Performing database backup using <fg=yellow>{$input->getArgument('database')}</> connection, please wait...");
        }

        // Execute backup with input arguments.
        $result = BackupService::db($input->getArguments());

        // Output the result of backup process.
        $output->writeln($result);
    }


}