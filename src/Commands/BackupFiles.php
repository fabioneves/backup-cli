<?php
namespace BackupCli\Commands;

use BackupCli\Services\Backup;
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
          ->setDescription('Backup a directory and compress it using the specified compressor.')
          ->addArgument('directory', InputArgument::REQUIRED, 'Directory to backup.')
          ->addOption('storage', 's', InputOption::VALUE_REQUIRED, 'Storage system where the backup will be saved')
          ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Directory to save the backup')
          ->addOption('compression', 'c', InputOption::VALUE_REQUIRED, 'Compression type (7zip, 7zip-ultra, 7zip-null, gzip, null)')
          ->addOption('exclude', 'e', InputOption::VALUE_REQUIRED, 'Directories to exclude (separate with a comma)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Show message of backup start process.
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("\n  Performing backup of <fg=yellow>{$input->getArgument('directory')}</>, please wait...");
        }

        // Execute backup with input arguments.
        $result = Backup::files(
          $input->getArgument('directory'),
          $input->getOption('storage'),
          $input->getOption('directory'),
          $input->getOption('compression'),
          $input->getOption('exclude')
        );

        // Output the result of backup process.
        $output->writeln($result);
    }


}