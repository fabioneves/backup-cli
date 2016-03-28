<?php
namespace BackupCli\Commands;

use BackupCli\Services\Restore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RestoreDb extends Command
{

    protected function configure()
    {
        $this->setName('restore:db')
          ->setDescription('Restore a database backup.')
          ->addArgument('database', InputArgument::REQUIRED, 'Database to restore')
          ->addArgument('storage', InputArgument::REQUIRED, 'Storage where the backup file is located')
          ->addArgument('backup_file_path', InputArgument::REQUIRED, 'Backup file path from the root of storage system')
          ->addArgument('compression', InputArgument::REQUIRED, 'Compression type (7zip, 7zip-ultra, 7zip-null, gzip)')
          ->addOption('parts', 'p', InputOption::VALUE_REQUIRED, 'How many backup file parts (if this is a multipart backup)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("
               <error>!!!!!!!!!! WARNING !!!!!!!!!!</error>

  Restoring a database can cause <fg=yellow>IRREVERSIBLE</> damage.
  You are about to <fg=red>DESTROY</> a complete database!

  Storage:</> <fg=blue>{$input->getArgument('storage')}</>
  Backup file:</> <fg=yellow>{$input->getArgument('backup_file_path')}</>
  Database profile:</> <fg=green>{$input->getArgument('database')}</>

  Do you really want to proceed [y/n]? ",
              false,
              '/^(y|j)/i'
            );

            // Shows a message if the process is cancelled
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln("\n  Database restore process <fg=yellow>cancelled</>.\n");

                return;
            }

            $output->writeln("\n  Restoring database backup using <fg=yellow>{$input->getArgument('database')}</> profile, please wait...");

        }
        // Execute backup with input arguments.
        $result = Restore::database(
          $input->getArgument('database'),
          $input->getArgument('storage'),
          $input->getArgument('backup_file_path'),
          $input->getArgument('compression'),
          $input->getOption('parts')
        );

        // Output the result of backup process.
        $output->writeln($result);
    }


}