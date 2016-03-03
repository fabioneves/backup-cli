<?php
namespace BackupCli\Commands;

use BackupCli\Services\Backup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupDb extends Command
{

    protected function configure()
    {
        $this->setName('backup:db')
          ->setDescription('Backup a database and compress it.')
          ->addArgument('database', InputArgument::REQUIRED, 'Database profile to backup (needs to exist in config).')
          ->addOption('storage', 's', InputOption::VALUE_REQUIRED, 'Storage system where the backup will be saved')
          ->addOption('compression', 'c', InputOption::VALUE_REQUIRED, 'Compression type (7zip, 7zip-ultra, 7zip-null, gzip, null)')
          ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Directory to store the backup in the storage system');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Show message of database backup start.
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("\n  Performing database backup using <fg=yellow>{$input->getArgument('database')}</> profile, please wait...");
        }

        // Execute backup with input arguments.
        $result = Backup::database(
          $input->getArgument('database'),
          $input->getOption('storage'),
          $input->getOption('compression'),
          $input->getOption('directory')
        );

        // Output the result of backup process.
        $output->writeln($result);
    }


}