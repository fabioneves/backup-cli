<?php
namespace BackupCli\Commands;

use BackupCli\Services\RestoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RestoreDb extends Command
{

    protected function configure()
    {
        $this->setName('restore:db')
          ->setDescription('Restore a database backup.')
          ->addArgument('filesystem', InputArgument::REQUIRED, 'Source filesystem to pull the backup file.')
          ->addArgument('filesystem_path', InputArgument::REQUIRED, 'Path of the backup file from the source filesystem root.')
          ->addArgument('database', InputArgument::REQUIRED, 'Database to be restored.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("
               <error>!!!!!!!!!! WARNING !!!!!!!!!!</error>

  Restoring a database can cause <fg=yellow>IRREVERSIBLE</> damage.
  You are about to <fg=red>DESTROY</> a complete database!

  <fg=green>Filesystem:</> <fg=yellow>{$input->getArgument('filesystem')}</>
  <fg=green>Backup file:</> <fg=yellow>{$input->getArgument('filesystem_path')}</>
  <fg=green>Database:</> <fg=yellow>{$input->getArgument('database')}</>

  Do you really want to proceed [y/n]? ",
              false,
              '/^(y|j)/i'
            );

            // Shows a message if the process is cancelled
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln("\n  Database restore process <fg=yellow>cancelled</>.\n");

                return;
            }

            $output->writeln("\n  Restoring database using <fg=yellow>{$input->getArgument('database')}</> connection, please wait...");

        }
        // Execute backup with input arguments.
        $result = RestoreService::db($input->getArguments());

        // Output the result of backup process.
        $output->writeln($result);
    }


}