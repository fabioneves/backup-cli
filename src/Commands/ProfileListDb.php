<?php
namespace BackupCli\Commands;

use BackupCli\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProfileListDb extends Command
{

    protected function configure()
    {
        $this->setName('profile:listdb')->setDescription('Lists all the existing database profiles.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Database data.
        $databases = Config::getFile('database');
        foreach ($databases as $profile => $data) {
            $dbs[] = [$profile, $data['type'], $data['database'], $data['user'], $data['host'], $data['port']];
        }

        // Output table.
        $table = new Table($output);
        $table->setHeaders(['Profile', 'Type', 'Database', 'Username', 'Server', 'Port'])->setRows($dbs);
        $table->render();
    }


}