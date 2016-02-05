<?php
namespace BackupCli\Commands;

use BackupCli\Providers\UpdateProvider;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdate extends Command
{

    protected function configure()
    {
        $this->setName('self-update')
          ->setDescription('Check and update this tool if there is a new version.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false);
        $updater->setStrategyObject(
          new UpdateProvider(
            $this->getApplication()->getVersion(),
            true,
            'http://fabioneves.github.io/backup-cli/manifest.json'
          )
        );

        try {
            $result = $updater->update();
            $message = $result ? '<info>Updated successfully!</info>' : 'You already have the latest version.';
            $output->writeln($message);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return;
        }
    }


}