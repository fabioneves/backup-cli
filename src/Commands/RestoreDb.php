<?php
namespace BackupCli\Commands;

use BackupCli\Services\RestoreService;
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
          ->addArgument('filesystem', InputArgument::REQUIRED, 'Source filesystem where the backup is')
          ->addArgument('filesystem_path', InputArgument::REQUIRED, 'Path of the backup file from the source filesystem root')
          ->addArgument('database', InputArgument::REQUIRED, 'Database connection that should be used to restore the database backup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Execute backup with input arguments.
        $result = RestoreService::db($input->getArguments());

        // Output the result of backup process.
        $output->writeln($result);
    }


}