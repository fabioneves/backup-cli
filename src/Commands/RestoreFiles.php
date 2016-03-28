<?php
namespace BackupCli\Commands;

use BackupCli\Services\Restore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RestoreFiles extends Command
{

    protected function configure()
    {
        $this->setName('restore:files')
          ->setDescription('Restore a file backup and extract the contents into a directory.')
          ->addArgument('storage', InputArgument::REQUIRED, 'Target filesystem where the backup is hosted')
          ->addArgument('backup_file_path', InputArgument::REQUIRED, 'Backup file path from the root of storage system')
          ->addArgument('destination_directory', InputArgument::REQUIRED, 'Directory to extract the contents of the backup file')
          ->addArgument('compression', InputArgument::REQUIRED, 'Compression type (7zip, 7zip-ultra, 7zip-null, gzip)')
          ->addOption('parts', 'p', InputOption::VALUE_REQUIRED, 'How many backup file parts (if this is a multipart backup)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("
               <error>!!!!!!!!!! WARNING !!!!!!!!!!</error>

  Restoring files can cause <fg=red>IRREVERSIBLE</> damage.
  All existing files in the <fg=yellow>destination directory</> will be <fg=red>REPLACED</>.

  Storage: <fg=blue>{$input->getArgument('storage')}</>
  Backup file: <fg=yellow>{$input->getArgument('backup_file_path')}</>
  Destination directory: <fg=green>{$input->getArgument('destination_directory')}</>

  Are you sure you want to proceed [y/n]? ",
              false,
              '/^(y|j)/i'
            );

            // Shows a message if the process is cancelled
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln("\n  Files restore process <fg=yellow>cancelled</>.\n");
                return;
            }

            $output->writeln("\n  Downloading and extracting backup to <fg=yellow>{$input->getArgument('destination_directory')}</>, please wait...");
        }


        // Execute backup with input arguments.
        $result = Restore::files(
          $input->getArgument('storage'),
          $input->getArgument('backup_file_path'),
          $input->getArgument('destination_directory'),
          $input->getArgument('compression'),
          $input->getOption('parts')
        );

        // Output the result of backup process.
        $output->writeln($result);
    }


}