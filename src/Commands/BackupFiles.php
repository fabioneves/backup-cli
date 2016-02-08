<?php
namespace BackupCli\Commands;

use BackupCli\Services\BackupService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupFiles extends Command
{

    protected function configure()
    {
        $this->setName('backup:files')
          ->setDescription('Backup a directory and compress it into a single file.')
          ->addArgument('backup_directory', InputArgument::REQUIRED, 'Directory to backup.')
          ->addArgument('target', InputArgument::REQUIRED, 'Target filesystem where the backup will be saved.')
          ->addArgument('target_directory', InputArgument::REQUIRED, 'Where to save the backup file on target filesystem.')
          ->addOption('exclude', null, InputOption::VALUE_REQUIRED, 'Directories to exclude (separate with a comma)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Show message of database backup start.
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("\n  Performing backup of <fg=yellow>{$input->getArgument('backup_directory')}</>, please wait...");
        }

        // Execute backup with input arguments.
        $result = BackupService::files($input->getArguments());

        // Output the result of backup process.
        $output->writeln($result);
    }


}