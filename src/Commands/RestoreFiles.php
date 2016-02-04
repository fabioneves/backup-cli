<?php
namespace BackupCli\Commands;

use BackupCli\Services\RestoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreFiles extends Command
{

    protected function configure()
    {
        $this->setName('restore:files')
          ->setDescription('Create a backup of a specified path and compress it.')
          ->addArgument('filesystem', InputArgument::REQUIRED, 'Target filesystem where the backup is hosted')
          ->addArgument('filesystem_path', InputArgument::REQUIRED, 'Target filesystem file path of the backup file to be retrieved')
          ->addArgument('destination_directory', InputArgument::REQUIRED, 'Directory to extract the contents of the backup file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Execute backup with input arguments.
        $result = RestoreService::files($input->getArguments());

        // Output the result of backup process.
        $output->writeln($result);
    }


}