<?php
namespace BackupCli\Commands;

use BackupCli\Services\RestoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RestoreFiles extends Command
{

    protected function configure()
    {
        $this->setName('restore:files')
          ->setDescription('Restore a file backup and extract the contents into a directory.')
          ->addArgument('filesystem', InputArgument::REQUIRED, 'Target filesystem where the backup is hosted')
          ->addArgument('filesystem_path', InputArgument::REQUIRED, 'File path of the backup in the target filesystem')
          ->addArgument('destination_directory', InputArgument::REQUIRED, 'Directory to extract the contents of the backup file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("
               <error>!!!!!!!!!! WARNING !!!!!!!!!!</error>

  Restoring files can cause <fg=red>IRREVERSIBLE</> damage.
  All existing files in the <fg=yellow>destination directory</> will be <fg=red>REPLACED</>.

  <fg=green>Filesystem:</> <fg=yellow>{$input->getArgument('filesystem')}</>
  <fg=green>Backup file:</> <fg=yellow>{$input->getArgument('filesystem_path')}</>
  <fg=green>Destination directory:</> <fg=yellow>{$input->getArgument('destination_directory')}</>

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
        $result = RestoreService::files($input->getArguments());

        // Output the result of backup process.
        $output->writeln($result);
    }


}