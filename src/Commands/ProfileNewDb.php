<?php
namespace BackupCli\Commands;

use BackupCli\Services\Profile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ProfileNewDb extends Command
{

    protected function configure()
    {
        $this->setName('profile:newdb')
          ->setDescription('Creates a new database profile.')
          ->addArgument('database', InputArgument::REQUIRED, 'Database name')
          ->addArgument('username', InputArgument::REQUIRED, 'Database username')
          ->addArgument('password', InputArgument::REQUIRED, 'Database password')
          ->addOption('server', 's', InputOption::VALUE_REQUIRED, 'Database server (default: localhost)')
          ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Database port (default: 3306)')
          ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Database type (mysql/postgresql)')
          ->addOption('profile', null, InputOption::VALUE_REQUIRED, 'Profile name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            // Profile name.
            $profile_name = empty($input->getOption('profile')) ? $input->getArgument('database') : $input->getOption('profile');
            // Database.
            $database_type = empty($input->getOption('type')) ? 'mysql' : $input->getOption('type');
            $database_port = empty($input->getOption('port')) ? 3306 : $input->getOption('port');
            $database_server = empty($input->getOption('server')) ? 'localhost' : $input->getOption('server');
            // Question.
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("
  <info>!!!!!!!!!! DATABASE PROFILE CREATION !!!!!!!!!!</info>

  Profile name: <fg=yellow>{$profile_name}</>
  Type: <fg=blue>{$database_type} ({$database_server}:{$database_port})</>
  Database: <fg=green>{$input->getArgument('database')}</>
  Username: <fg=cyan>{$input->getArgument('username')}</>
  Password: <fg=red>{$input->getArgument('password')}</>

  Do you want to proceed with database profile creation [y/n]? ",
              false,
              '/^(y|j)/i'
            );

            // Shows a message if the process is cancelled
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln("\n  Database profile creation <fg=yellow>cancelled</>.\n");

                return;
            }

            $output->writeln("\n  Creating database profile <fg=yellow>{$profile_name}</>, please wait...");
        }

        // Creates new database profile.
        $result = Profile::newDatabase(
          $input->getArgument('database'),
          $input->getArgument('username'),
          $input->getArgument('password'),
          $profile_name,
          $database_type,
          $database_server,
          $database_port
        );

        // Output the result.
        $output->writeln($result);

    }


}